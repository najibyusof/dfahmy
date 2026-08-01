<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_report_filters_require_valid_dates(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $this->actingAs($manager)
            ->from(route('reports.index'))
            ->get(route('reports.index', ['from' => 'invalid-date', 'to' => '2026-08-01']))
            ->assertRedirect(route('reports.index'))
            ->assertSessionHasErrors(['from']);
    }

    public function test_report_export_endpoint_is_rate_limited(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($manager)
                ->get(route('reports.export', ['type' => 'revenue_summary']))
                ->assertOk();
        }

        $this->actingAs($manager)
            ->get(route('reports.export', ['type' => 'revenue_summary']))
            ->assertStatus(429);
    }

    public function test_payment_cannot_be_recorded_for_another_user_without_user_management_permission(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $otherStaff = User::factory()->create();

        $booking = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'total_amount' => 900,
        ]);

        $this->actingAs($manager)
            ->from(route('payments.create'))
            ->post(route('payments.store'), [
                'booking_id' => $booking->id,
                'receipt_number' => 'RCPT-HARD-1001',
                'payment_date' => now()->toDateString(),
                'amount' => 300,
                'payment_method' => 'cash',
                'reference_number' => null,
                'payment_status' => 'paid',
                'received_by_user_id' => $otherStaff->id,
                'notes' => 'Attempt to spoof receiver',
            ])
            ->assertRedirect(route('payments.create'))
            ->assertSessionHasErrors(['received_by_user_id']);
    }
}
