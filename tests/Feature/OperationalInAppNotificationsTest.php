<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\HousekeepingTask;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OperationalInAppNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_booking_lifecycle_notifications_are_created_and_scoped_to_authorized_users(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $guest = Guest::factory()->create();
        $room = Room::factory()->create();

        $this->actingAs($receptionist)
            ->post(route('bookings.store'), $this->bookingPayload($guest->id, $room->id, 'BK-NOTIF-01', 'pending'))
            ->assertRedirect(route('bookings.index'));

        $booking = Booking::query()->where('booking_reference', 'BK-NOTIF-01')->firstOrFail();

        $this->actingAs($receptionist)
            ->patch(route('bookings.update', $booking), $this->bookingPayload($guest->id, $room->id, 'BK-NOTIF-01', 'confirmed'))
            ->assertRedirect(route('bookings.index'));

        $this->actingAs($receptionist)
            ->patch(route('bookings.check-in', $booking))
            ->assertRedirect(route('bookings.show', $booking));

        $this->actingAs($receptionist)
            ->patch(route('bookings.check-out', $booking))
            ->assertRedirect(route('bookings.show', $booking));

        $this->actingAs($receptionist)
            ->post(route('bookings.store'), $this->bookingPayload($guest->id, $room->id, 'BK-NOTIF-02', 'pending'))
            ->assertRedirect(route('bookings.index'));

        $bookingToCancel = Booking::query()->where('booking_reference', 'BK-NOTIF-02')->firstOrFail();

        $this->actingAs($receptionist)
            ->patch(route('bookings.cancel', $bookingToCancel))
            ->assertRedirect(route('bookings.show', $bookingToCancel));

        $managerTypes = $manager->fresh()->notifications()->get()->pluck('data.type')->all();

        $this->assertContains('booking_new', $managerTypes);
        $this->assertContains('booking_confirmed', $managerTypes);
        $this->assertContains('booking_outstanding_before_check_in', $managerTypes);
        $this->assertContains('booking_check_in', $managerTypes);
        $this->assertContains('booking_check_out', $managerTypes);
        $this->assertContains('booking_cancelled', $managerTypes);

        $housekeeperTypes = $housekeeper->fresh()->notifications()->get()->pluck('data.type')->all();
        $this->assertNotContains('booking_new', $housekeeperTypes);
    }

    public function test_new_payment_notification_is_sent_to_authorized_users_only(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $booking = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'total_amount' => 800,
        ]);

        $this->actingAs($receptionist)
            ->post(route('payments.store'), [
                'booking_id' => $booking->id,
                'receipt_number' => 'RCPT-NOTIF-1001',
                'payment_date' => now()->toDateString(),
                'amount' => 300,
                'payment_method' => 'cash',
                'reference_number' => null,
                'payment_status' => 'paid',
                'received_by_user_id' => $receptionist->id,
                'notes' => 'Payment test',
            ])
            ->assertRedirect();

        $managerTypes = $manager->fresh()->notifications()->get()->pluck('data.type')->all();
        $housekeeperTypes = $housekeeper->fresh()->notifications()->get()->pluck('data.type')->all();

        $this->assertContains('payment_new', $managerTypes);
        $this->assertNotContains('payment_new', $housekeeperTypes);

        $this->assertDatabaseCount('payments', 1);
        $this->assertInstanceOf(Payment::class, Payment::query()->first());
    }

    public function test_housekeeping_maintenance_and_room_status_notifications_are_created(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $room = Room::factory()->create(['status' => 'available']);

        $this->actingAs($manager)
            ->post(route('housekeeping.manage.store'), [
                'room_id' => $room->id,
                'booking_id' => null,
                'assigned_to_user_id' => $housekeeper->id,
                'task_type' => 'maintenance',
                'priority' => 'high',
                'due_date' => now()->subDay()->toDateString(),
                'status' => 'pending',
                'notes' => 'Fix AC unit',
                'checklist_notes' => 'Awaiting inspection',
                'completed_at' => null,
            ])
            ->assertRedirect(route('housekeeping.manage.index'));

        $maintenanceTask = HousekeepingTask::query()->where('task_type', 'maintenance')->latest('id')->firstOrFail();

        $this->actingAs($housekeeper)
            ->patch(route('housekeeping.tasks.update', $maintenanceTask), [
                'status' => 'completed',
                'checklist_notes' => 'Resolved',
            ])
            ->assertRedirect(route('housekeeping.tasks.index'));

        HousekeepingTask::factory()->create([
            'assigned_to_user_id' => $housekeeper->id,
            'room_id' => $room->id,
            'task_type' => 'deep_cleaning',
            'priority' => 'medium',
            'due_date' => now()->subDays(2)->toDateString(),
            'status' => 'pending',
        ]);

        Artisan::call('housekeeping:notify-overdue');

        $this->actingAs($manager)
            ->patch(route('rooms.update', $room), [
                'building_id' => $room->building_id,
                'name' => $room->name,
                'code' => $room->code,
                'floor' => $room->floor,
                'room_type' => $room->room_type,
                'status' => 'out_of_service',
                'base_nightly_rate' => $room->base_nightly_rate,
                'maximum_guests' => $room->maximum_guests,
                'notes' => $room->notes,
                'is_active' => $room->is_active,
            ])
            ->assertRedirect(route('rooms.index'));

        $managerTypes = $manager->fresh()->notifications()->get()->pluck('data.type')->all();
        $housekeeperTypes = $housekeeper->fresh()->notifications()->get()->pluck('data.type')->all();

        $this->assertContains('maintenance_request_created', $managerTypes);
        $this->assertContains('maintenance_request_assigned', $managerTypes);
        $this->assertContains('maintenance_request_resolved', $managerTypes);
        $this->assertContains('room_status_maintenance_or_out_of_service', $managerTypes);
        $this->assertContains('housekeeping_task_overdue', $housekeeperTypes);
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPayload(int $guestId, int $roomId, string $reference, string $status): array
    {
        return [
            'booking_reference' => $reference,
            'guest_id' => $guestId,
            'check_in_date' => now()->addDays(1)->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'booking_source' => 'website',
            'booking_status' => $status,
            'special_requests' => null,
            'internal_notes' => null,
            'subtotal' => 500,
            'discount' => 0,
            'tax' => 30,
            'total_amount' => 530,
            'items' => [
                [
                    'room_id' => $roomId,
                    'nightly_rate' => 250,
                    'adults' => 2,
                    'children' => 0,
                    'check_in_date' => now()->addDays(1)->toDateString(),
                    'check_out_date' => now()->addDays(3)->toDateString(),
                ],
            ],
        ];
    }
}
