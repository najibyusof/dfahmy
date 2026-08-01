<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_housekeeper_cannot_access_payment_module_routes(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $payment = Payment::factory()->create();

        $this->actingAs($housekeeper)->get(route('payments.index'))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('payments.create'))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('payments.show', $payment))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('payments.refund.page', $payment))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('payments.void.page', $payment))->assertForbidden();
    }

    public function test_receptionist_can_record_multiple_payments_and_booking_summary_updates(): void
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $booking = Booking::factory()->create([
            'total_amount' => 1000,
            'booking_status' => 'confirmed',
        ]);

        $this->actingAs($receptionist)->post(route('payments.store'), [
            'booking_id' => $booking->id,
            'receipt_number' => 'RCPT-1001',
            'payment_date' => now()->toDateString(),
            'amount' => 400,
            'payment_method' => 'cash',
            'reference_number' => null,
            'payment_status' => 'paid',
            'received_by_user_id' => $receptionist->id,
            'notes' => 'Deposit received',
        ])->assertRedirect();

        $this->actingAs($receptionist)->post(route('payments.store'), [
            'booking_id' => $booking->id,
            'receipt_number' => 'RCPT-1002',
            'payment_date' => now()->toDateString(),
            'amount' => 300,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-ABC',
            'payment_status' => 'paid',
            'received_by_user_id' => $receptionist->id,
            'notes' => 'Second payment',
        ])->assertRedirect();

        $booking->refresh()->load('payments');

        $this->assertSame(2, $booking->payments->count());
        $this->assertEquals(700.0, $booking->totalPaidAmount());
        $this->assertEquals(300.0, $booking->outstandingBalanceAmount());
        $this->assertSame('partially_paid', $booking->paymentSummaryStatus());
    }

    public function test_overpayment_is_blocked_without_override_permission(): void
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $booking = Booking::factory()->create([
            'total_amount' => 500,
            'booking_status' => 'confirmed',
        ]);

        Payment::factory()->create([
            'booking_id' => $booking->id,
            'payment_status' => 'paid',
            'amount' => 400,
            'received_by_user_id' => $receptionist->id,
        ]);

        $this->actingAs($receptionist)
            ->from(route('payments.create'))
            ->post(route('payments.store'), [
                'booking_id' => $booking->id,
                'receipt_number' => 'RCPT-OVER-01',
                'payment_date' => now()->toDateString(),
                'amount' => 200,
                'payment_method' => 'cash',
                'reference_number' => null,
                'payment_status' => 'paid',
                'received_by_user_id' => $receptionist->id,
                'notes' => null,
            ])
            ->assertRedirect(route('payments.create'))
            ->assertSessionHasErrors(['amount']);
    }

    public function test_manager_with_override_permission_can_record_overpayment(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $booking = Booking::factory()->create([
            'total_amount' => 500,
            'booking_status' => 'confirmed',
        ]);

        $this->actingAs($manager)->post(route('payments.store'), [
            'booking_id' => $booking->id,
            'receipt_number' => 'RCPT-OVER-ALLOW',
            'payment_date' => now()->toDateString(),
            'amount' => 700,
            'payment_method' => 'online_gateway',
            'reference_number' => 'PG-7788',
            'payment_status' => 'paid',
            'received_by_user_id' => $manager->id,
            'notes' => 'Corporate bulk settlement',
        ])->assertRedirect();

        $booking->refresh()->load('payments');

        $this->assertEquals(700.0, $booking->totalPaidAmount());
        $this->assertSame('overpaid', $booking->paymentSummaryStatus());
    }

    public function test_refund_and_void_actions_update_payment_statuses(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $paidPayment = Payment::factory()->create([
            'payment_status' => 'paid',
            'received_by_user_id' => $manager->id,
        ]);

        $pendingPayment = Payment::factory()->create([
            'payment_status' => 'pending',
            'received_by_user_id' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->patch(route('payments.refund', $paidPayment), ['notes' => 'Guest cancellation'])
            ->assertRedirect(route('payments.show', $paidPayment));

        $this->actingAs($manager)
            ->patch(route('payments.void', $pendingPayment), ['notes' => 'Duplicate attempt'])
            ->assertRedirect(route('payments.show', $pendingPayment));

        $this->assertDatabaseHas('payments', [
            'id' => $paidPayment->id,
            'payment_status' => 'refunded',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $pendingPayment->id,
            'payment_status' => 'voided',
        ]);
    }

    public function test_printable_invoice_and_receipt_pages_are_accessible(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $payment = Payment::factory()->create([
            'payment_status' => 'paid',
            'received_by_user_id' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get(route('bookings.invoice', $payment->booking))
            ->assertOk()
            ->assertSeeText('Print Invoice');

        $this->actingAs($manager)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSeeText('Print Receipt');
    }

    public function test_dashboard_displays_unpaid_and_partially_paid_booking_metrics(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $unpaidBooking = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'total_amount' => 600,
        ]);

        $partialBooking = Booking::factory()->create([
            'booking_status' => 'pending',
            'total_amount' => 1000,
        ]);
        Payment::factory()->create([
            'booking_id' => $partialBooking->id,
            'payment_status' => 'paid',
            'amount' => 300,
            'received_by_user_id' => $manager->id,
        ]);

        $fullyPaidBooking = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'total_amount' => 500,
        ]);
        Payment::factory()->create([
            'booking_id' => $fullyPaidBooking->id,
            'payment_status' => 'paid',
            'amount' => 500,
            'received_by_user_id' => $manager->id,
        ]);

        $cancelledBooking = Booking::factory()->create([
            'booking_status' => 'cancelled',
            'total_amount' => 800,
        ]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Unpaid Bookings')
            ->assertSee('Partially Paid Bookings')
            ->assertSee((string) 1)
            ->assertSee((string) 1);

        $this->assertNotNull($unpaidBooking);
        $this->assertNotNull($cancelledBooking);
    }
}
