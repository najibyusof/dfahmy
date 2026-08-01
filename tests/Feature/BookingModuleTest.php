<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Building;
use App\Models\BookingRoomItem;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_housekeeper_cannot_access_booking_module_routes(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $booking = Booking::factory()->create();

        $this->actingAs($housekeeper)->get(route('bookings.index'))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('bookings.create'))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('bookings.show', $booking))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('bookings.cancel.page', $booking))->assertForbidden();
    }

    public function test_receptionist_can_create_update_search_and_delete_multi_room_booking(): void
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $guest = Guest::factory()->create(['full_name' => 'Aisyah Guest']);
        $roomA = Room::factory()->create(['code' => 'RM900']);
        $roomB = Room::factory()->create(['code' => 'RM901']);

        $this->actingAs($receptionist)->post(route('bookings.store'), [
            'booking_reference' => 'BK-1001',
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDays(1)->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'adults' => 3,
            'children' => 1,
            'booking_source' => 'website',
            'booking_status' => 'confirmed',
            'special_requests' => 'Near pool',
            'internal_notes' => 'VIP',
            'subtotal' => 1200,
            'discount' => 100,
            'tax' => 66,
            'total_amount' => 1166,
            'items' => [
                [
                    'room_id' => $roomA->id,
                    'nightly_rate' => 450,
                    'adults' => 2,
                    'children' => 1,
                    'check_in_date' => now()->addDays(1)->toDateString(),
                    'check_out_date' => now()->addDays(3)->toDateString(),
                ],
                [
                    'room_id' => $roomB->id,
                    'nightly_rate' => 550,
                    'adults' => 1,
                    'children' => 0,
                    'check_in_date' => now()->addDays(1)->toDateString(),
                    'check_out_date' => now()->addDays(3)->toDateString(),
                ],
            ],
        ])->assertRedirect(route('bookings.index'));

        $booking = Booking::query()->where('booking_reference', 'BK-1001')->firstOrFail();
        $this->assertEquals(2, $booking->bookingRoomItems()->count());

        $this->actingAs($receptionist)->patch(route('bookings.update', $booking), [
            'booking_reference' => 'BK-1001-UPDATED',
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'booking_source' => 'phone',
            'booking_status' => 'pending',
            'special_requests' => 'Updated',
            'internal_notes' => 'Updated note',
            'subtotal' => 900,
            'discount' => 50,
            'tax' => 42,
            'total_amount' => 892,
            'items' => [
                [
                    'room_id' => $roomA->id,
                    'nightly_rate' => 450,
                    'adults' => 2,
                    'children' => 0,
                    'check_in_date' => now()->addDays(2)->toDateString(),
                    'check_out_date' => now()->addDays(4)->toDateString(),
                ],
            ],
        ])->assertRedirect(route('bookings.index'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_reference' => 'BK-1001-UPDATED',
            'booking_status' => 'pending',
        ]);

        $this->assertEquals(1, BookingRoomItem::query()->where('booking_id', $booking->id)->count());

        $this->actingAs($receptionist)
            ->get(route('bookings.index', ['search' => 'BK-1001-UPDATED']))
            ->assertOk()
            ->assertSee('BK-1001-UPDATED')
            ->assertSee('Aisyah Guest');

        $this->actingAs($receptionist)
            ->delete(route('bookings.destroy', $booking))
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function test_booking_validation_rejects_invalid_dates_and_duplicate_reference(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();

        Booking::factory()->create([
            'booking_reference' => 'BK-DUPLICATE',
        ]);

        $this->actingAs($manager)
            ->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'booking_reference' => 'BK-DUPLICATE',
                'guest_id' => $guest->id,
                'check_in_date' => now()->addDays(5)->toDateString(),
                'check_out_date' => now()->addDays(2)->toDateString(),
                'adults' => 2,
                'children' => 0,
                'booking_source' => 'website',
                'booking_status' => 'pending',
                'subtotal' => 100,
                'discount' => 0,
                'tax' => 0,
                'total_amount' => 100,
                'items' => [],
            ])
            ->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors(['booking_reference', 'check_out_date', 'items']);
    }

    public function test_room_availability_search_filters_by_date_guest_count_building_and_room(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $buildingA = Building::factory()->create(['name' => 'Main House']);
        $buildingB = Building::factory()->create(['name' => 'Pool House']);

        $roomA = Room::factory()->create([
            'building_id' => $buildingA->id,
            'code' => 'MAIN-101',
            'maximum_guests' => 4,
        ]);

        $roomB = Room::factory()->create([
            'building_id' => $buildingB->id,
            'code' => 'POOL-201',
            'maximum_guests' => 2,
        ]);

        $response = $this->actingAs($manager)->get(route('bookings.create', [
            'availability_check_in' => '2026-08-10',
            'availability_check_out' => '2026-08-12',
            'availability_adults' => 2,
            'availability_children' => 1,
            'availability_building_id' => $buildingA->id,
            'availability_room_id' => $roomA->id,
        ]));

        $response->assertOk();
        $response->assertSee('Available rooms found: 1');
        $response->assertSee('MAIN-101');
        $response->assertDontSee('Selected room does not have enough guest capacity.');
    }

    public function test_overlapping_active_booking_for_same_room_is_blocked(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();
        $room = Room::factory()->create(['maximum_guests' => 4]);

        $existing = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-12',
        ]);

        $existing->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 400,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-12',
        ]);

        $this->actingAs($manager)
            ->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'booking_reference' => 'BLOCK-OVERLAP',
                'guest_id' => $guest->id,
                'check_in_date' => '2026-08-11',
                'check_out_date' => '2026-08-13',
                'adults' => 2,
                'children' => 0,
                'booking_source' => 'walk_in',
                'booking_status' => 'pending',
                'subtotal' => 600,
                'discount' => 0,
                'tax' => 36,
                'total_amount' => 636,
                'items' => [[
                    'room_id' => $room->id,
                    'nightly_rate' => 300,
                    'adults' => 2,
                    'children' => 0,
                    'check_in_date' => '2026-08-11',
                    'check_out_date' => '2026-08-13',
                ]],
            ])
            ->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors(['items.0.room_id']);
    }

    public function test_cancelled_and_no_show_bookings_do_not_block_availability(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $room = Room::factory()->create(['code' => 'FREE-ROOM']);

        $cancelled = Booking::factory()->create([
            'booking_status' => 'cancelled',
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-12',
        ]);
        $cancelled->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 400,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-12',
        ]);

        $noShow = Booking::factory()->create([
            'booking_status' => 'no_show',
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-12',
        ]);
        $noShow->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 400,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => '2026-08-10',
            'check_out_date' => '2026-08-12',
        ]);

        $response = $this->actingAs($manager)->get(route('bookings.create', [
            'availability_check_in' => '2026-08-10',
            'availability_check_out' => '2026-08-12',
            'availability_adults' => 1,
            'availability_children' => 0,
        ]));

        $response->assertOk();
        $response->assertSee('FREE-ROOM');
    }

    public function test_guest_profile_links_to_prefilled_booking_creation_page(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();

        $this->actingAs($manager)
            ->get(route('guests.show', $guest))
            ->assertSee(route('bookings.create', ['guest' => $guest->id]));

        $this->actingAs($manager)
            ->get(route('bookings.create', ['guest' => $guest->id]))
            ->assertOk()
            ->assertSee('New Booking');
    }

    public function test_booking_status_pages_and_actions_work(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $booking = Booking::factory()->create([
            'booking_status' => 'confirmed',
        ]);

        $this->actingAs($manager)
            ->get(route('bookings.cancel.page', $booking))
            ->assertOk();

        $this->actingAs($manager)
            ->patch(route('bookings.cancel', $booking))
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'cancelled',
        ]);

        $booking->refresh();
        $booking->update(['booking_status' => 'confirmed']);

        $this->actingAs($manager)
            ->get(route('bookings.check-in.page', $booking))
            ->assertOk();

        $this->actingAs($manager)
            ->patch(route('bookings.check-in', $booking))
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'checked_in',
        ]);

        $this->actingAs($manager)
            ->get(route('bookings.check-out.page', $booking))
            ->assertOk();

        $this->actingAs($manager)
            ->patch(route('bookings.check-out', $booking))
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'checked_out',
        ]);
    }

    public function test_bookings_index_can_filter_by_payment_summary_status(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $unpaid = Booking::factory()->create([
            'booking_reference' => 'PAY-UNPAID-001',
            'total_amount' => 1000,
        ]);

        $partial = Booking::factory()->create([
            'booking_reference' => 'PAY-PARTIAL-001',
            'total_amount' => 1000,
        ]);
        Payment::factory()->create([
            'booking_id' => $partial->id,
            'payment_status' => 'paid',
            'amount' => 300,
            'received_by_user_id' => $manager->id,
        ]);

        $paid = Booking::factory()->create([
            'booking_reference' => 'PAY-PAID-001',
            'total_amount' => 1000,
        ]);
        Payment::factory()->create([
            'booking_id' => $paid->id,
            'payment_status' => 'paid',
            'amount' => 1000,
            'received_by_user_id' => $manager->id,
        ]);

        $overpaid = Booking::factory()->create([
            'booking_reference' => 'PAY-OVERPAID-001',
            'total_amount' => 1000,
        ]);
        Payment::factory()->create([
            'booking_id' => $overpaid->id,
            'payment_status' => 'paid',
            'amount' => 1200,
            'received_by_user_id' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->get(route('bookings.index', ['payment_summary' => 'unpaid']))
            ->assertOk()
            ->assertSee('PAY-UNPAID-001')
            ->assertDontSee('PAY-PARTIAL-001')
            ->assertDontSee('PAY-PAID-001')
            ->assertDontSee('PAY-OVERPAID-001');

        $this->actingAs($manager)
            ->get(route('bookings.index', ['payment_summary' => 'partially_paid']))
            ->assertOk()
            ->assertSee('PAY-PARTIAL-001')
            ->assertDontSee('PAY-UNPAID-001')
            ->assertDontSee('PAY-PAID-001')
            ->assertDontSee('PAY-OVERPAID-001');

        $this->actingAs($manager)
            ->get(route('bookings.index', ['payment_summary' => 'paid']))
            ->assertOk()
            ->assertSee('PAY-PAID-001')
            ->assertDontSee('PAY-UNPAID-001')
            ->assertDontSee('PAY-PARTIAL-001')
            ->assertDontSee('PAY-OVERPAID-001');

        $this->actingAs($manager)
            ->get(route('bookings.index', ['payment_summary' => 'overpaid']))
            ->assertOk()
            ->assertSee('PAY-OVERPAID-001')
            ->assertDontSee('PAY-UNPAID-001')
            ->assertDontSee('PAY-PARTIAL-001')
            ->assertDontSee('PAY-PAID-001');

        $this->actingAs($manager)
            ->get(route('bookings.index', ['quick' => 'unpaid']))
            ->assertOk()
            ->assertSee('PAY-UNPAID-001')
            ->assertDontSee('PAY-PARTIAL-001')
            ->assertDontSee('PAY-PAID-001')
            ->assertDontSee('PAY-OVERPAID-001');

        $this->actingAs($manager)
            ->get(route('bookings.index', ['quick' => 'partially_paid']))
            ->assertOk()
            ->assertSee('PAY-PARTIAL-001')
            ->assertDontSee('PAY-UNPAID-001')
            ->assertDontSee('PAY-PAID-001')
            ->assertDontSee('PAY-OVERPAID-001');
    }
}
