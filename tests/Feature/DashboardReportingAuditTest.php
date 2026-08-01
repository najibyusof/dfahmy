<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use App\Services\AuditLogService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportingAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_dashboard_shows_required_operational_widgets(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();
        $room = Room::factory()->create(['status' => 'cleaning']);

        $booking = Booking::factory()->create([
            'guest_id' => $guest->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->toDateString(),
            'booking_status' => 'checked_in',
        ]);
        $booking->bookingRoomItems()->delete();
        $booking->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 300,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
        ]);

        HousekeepingTask::factory()->create([
            'room_id' => $room->id,
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText("Today's Arrivals")
            ->assertSeeText("Today's Departures")
            ->assertSeeText('Current Guests')
            ->assertSeeText('Available Rooms')
            ->assertSeeText('Occupied Rooms')
            ->assertSeeText('Rooms Needing Cleaning')
            ->assertSeeText('Rooms Under Maintenance')
            ->assertSeeText('Expected Payments Today')
            ->assertSeeText('Outstanding Balances')
            ->assertSeeText('Recent Bookings')
            ->assertSeeText('Urgent Tasks');
    }

    public function test_reports_page_and_csv_export_are_available_to_authorized_users_only(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $this->actingAs($receptionist)
            ->get(route('reports.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('reports.index', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Occupancy by Building')
            ->assertSee('Booking Source Summary')
            ->assertSee('Revenue Summary')
            ->assertSee('Payment Method Summary')
            ->assertSee('Outstanding Balance Report')
            ->assertSee('Housekeeping Report')
            ->assertSee('Maintenance Report');

        $this->actingAs($manager)
            ->get(route('reports.export', [
                'type' => 'booking_source_summary',
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_audit_log_records_create_update_delete_and_status_change_and_redacts_sensitive_values(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();

        $this->actingAs($manager)
            ->post(route('guests.store'), [
                'full_name' => 'Audit Guest',
                'email' => 'audit-guest@example.com',
                'phone_number' => '+60123456789',
                'identification_number' => 'ID-9988',
                'address' => 'Address',
                'nationality' => 'MY',
                'emergency_contact_name' => 'EC',
                'emergency_contact_phone' => '+60123456780',
                'notes' => 'test',
            ])
            ->assertRedirect(route('guests.index'));

        $createdGuest = Guest::query()->where('email', 'audit-guest@example.com')->firstOrFail();

        $this->actingAs($manager)
            ->patch(route('guests.update', $createdGuest), [
                'full_name' => 'Audit Guest Updated',
                'email' => 'audit-guest@example.com',
                'phone_number' => '+60123456789',
                'identification_number' => 'ID-9988',
                'address' => 'Address',
                'nationality' => 'MY',
                'emergency_contact_name' => 'EC',
                'emergency_contact_phone' => '+60123456780',
                'notes' => 'updated',
            ])
            ->assertRedirect(route('guests.index'));

        $room = Room::factory()->create();
        $booking = Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_status' => 'pending',
        ]);
        $booking->bookingRoomItems()->delete();
        $booking->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 300,
            'adults' => 1,
            'children' => 0,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($manager)
            ->patch(route('bookings.update', $booking), [
                'booking_reference' => $booking->booking_reference,
                'guest_id' => $guest->id,
                'check_in_date' => now()->addDay()->toDateString(),
                'check_out_date' => now()->addDays(2)->toDateString(),
                'adults' => 1,
                'children' => 0,
                'booking_source' => 'website',
                'booking_status' => 'confirmed',
                'special_requests' => null,
                'internal_notes' => null,
                'subtotal' => 300,
                'discount' => 0,
                'tax' => 18,
                'total_amount' => 318,
                'items' => [[
                    'room_id' => $room->id,
                    'nightly_rate' => 300,
                    'adults' => 1,
                    'children' => 0,
                    'check_in_date' => now()->addDay()->toDateString(),
                    'check_out_date' => now()->addDays(2)->toDateString(),
                ]],
            ])
            ->assertRedirect(route('bookings.index'));

        $this->actingAs($manager)
            ->delete(route('guests.destroy', $createdGuest))
            ->assertRedirect(route('guests.index'));

        $this->assertDatabaseHas('audit_logs', ['action' => 'create', 'subject_type' => Guest::class]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'update', 'subject_type' => Booking::class]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'delete', 'subject_type' => Guest::class]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'status_change', 'subject_type' => Booking::class]);

        $auditLogService = app(AuditLogService::class);
        $auditLogService->record('update', $booking, ['api_token' => 'secret-old'], ['api_token' => 'secret-new']);

        $redacted = AuditLog::query()->latest('id')->firstOrFail();
        $this->assertSame('[REDACTED]', $redacted->old_values['api_token']);
        $this->assertSame('[REDACTED]', $redacted->new_values['api_token']);
    }

    public function test_audit_logs_visible_to_manager_and_super_admin_only(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $this->actingAs($superAdmin)->get(route('audit-logs.index'))->assertOk();
        $this->actingAs($manager)->get(route('audit-logs.index'))->assertOk();
        $this->actingAs($receptionist)->get(route('audit-logs.index'))->assertForbidden();
    }
}
