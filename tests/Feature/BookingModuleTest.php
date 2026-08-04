<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookableUnit;
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

    public function test_room_booking_blocks_overlapping_group_booking_for_same_dates(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();
        $roomA = Room::factory()->create(['code' => 'POOL-A', 'maximum_guests' => 4]);
        $roomB = Room::factory()->create(['code' => 'POOL-B', 'maximum_guests' => 4]);

        $poolGroup = $this->createBookableUnit('All Pool House Rooms', 'UNIT-POOL-ALL-TEST', 'room_group', 1200, 8, [$roomA, $roomB]);

        $existing = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'check_in_date' => '2026-09-10',
            'check_out_date' => '2026-09-12',
        ]);

        $existing->bookingRoomItems()->create([
            'room_id' => $roomA->id,
            'nightly_rate' => 600,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => '2026-09-10',
            'check_out_date' => '2026-09-12',
        ]);

        $this->actingAs($manager)
            ->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'booking_reference' => 'GROUP-CONFLICT-01',
                'guest_id' => $guest->id,
                'check_in_date' => '2026-09-10',
                'check_out_date' => '2026-09-12',
                'adults' => 4,
                'children' => 0,
                'booking_source' => 'website',
                'booking_status' => 'pending',
                'subtotal' => 2400,
                'discount' => 0,
                'tax' => 144,
                'total_amount' => 2544,
                'items' => [[
                    'bookable_unit_id' => $poolGroup->id,
                    'nightly_rate' => 1200,
                    'adults' => 4,
                    'children' => 0,
                    'check_in_date' => '2026-09-10',
                    'check_out_date' => '2026-09-12',
                ]],
            ])
            ->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors(['items.0.bookable_unit_id']);
    }

    public function test_overlapping_group_bookings_with_shared_rooms_are_blocked(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();
        $room1 = Room::factory()->create(['code' => 'MAIN-G1', 'maximum_guests' => 4]);
        $room2 = Room::factory()->create(['code' => 'MAIN-G2', 'maximum_guests' => 4]);
        $room3 = Room::factory()->create(['code' => 'MAIN-F1', 'maximum_guests' => 4]);

        $groundFloor = $this->createBookableUnit('Main Ground Floor', 'UNIT-MAIN-GROUND-TEST', 'floor', 1600, 6, [$room1, $room2]);
        $entireMain = $this->createBookableUnit('Entire Main House', 'UNIT-MAIN-ALL-TEST', 'building', 3000, 10, [$room1, $room2, $room3]);

        $existing = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'check_in_date' => '2026-10-01',
            'check_out_date' => '2026-10-03',
        ]);

        $existing->bookingRoomItems()->create([
            'bookable_unit_id' => $groundFloor->id,
            'bookable_unit_name' => $groundFloor->name,
            'bookable_unit_code' => $groundFloor->code,
            'booking_type' => $groundFloor->booking_type,
            'included_rooms_snapshot' => [
                ['room_id' => $room1->id, 'room_code' => $room1->code, 'room_name' => $room1->name],
                ['room_id' => $room2->id, 'room_code' => $room2->code, 'room_name' => $room2->name],
            ],
            'room_id' => $room1->id,
            'nightly_rate' => 1600,
            'adults' => 4,
            'children' => 0,
            'check_in_date' => '2026-10-01',
            'check_out_date' => '2026-10-03',
        ]);

        $this->actingAs($manager)
            ->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'booking_reference' => 'GROUP-CONFLICT-02',
                'guest_id' => $guest->id,
                'check_in_date' => '2026-10-01',
                'check_out_date' => '2026-10-03',
                'adults' => 6,
                'children' => 0,
                'booking_source' => 'website',
                'booking_status' => 'pending',
                'subtotal' => 6000,
                'discount' => 0,
                'tax' => 360,
                'total_amount' => 6360,
                'items' => [[
                    'bookable_unit_id' => $entireMain->id,
                    'nightly_rate' => 3000,
                    'adults' => 6,
                    'children' => 0,
                    'check_in_date' => '2026-10-01',
                    'check_out_date' => '2026-10-03',
                ]],
            ])
            ->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors(['items.0.bookable_unit_id']);
    }

    public function test_whole_resort_booking_blocks_other_units_that_share_any_room(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();
        $roomA = Room::factory()->create(['code' => 'RS-A']);
        $roomB = Room::factory()->create(['code' => 'RS-B']);

        $wholeResort = $this->createBookableUnit('Whole Resort', 'UNIT-RESORT-ALL-TEST', 'whole_resort', 5000, 20, [$roomA, $roomB]);
        $singleRoom = $this->createBookableUnit('Room A', 'UNIT-ROOM-A-TEST', 'room', 500, 2, [$roomA]);

        $existing = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'check_in_date' => '2026-11-20',
            'check_out_date' => '2026-11-22',
        ]);

        $existing->bookingRoomItems()->create([
            'bookable_unit_id' => $wholeResort->id,
            'bookable_unit_name' => $wholeResort->name,
            'bookable_unit_code' => $wholeResort->code,
            'booking_type' => $wholeResort->booking_type,
            'included_rooms_snapshot' => [
                ['room_id' => $roomA->id, 'room_code' => $roomA->code, 'room_name' => $roomA->name],
                ['room_id' => $roomB->id, 'room_code' => $roomB->code, 'room_name' => $roomB->name],
            ],
            'room_id' => $roomA->id,
            'nightly_rate' => 5000,
            'adults' => 10,
            'children' => 0,
            'check_in_date' => '2026-11-20',
            'check_out_date' => '2026-11-22',
        ]);

        $this->actingAs($manager)
            ->from(route('bookings.create'))
            ->post(route('bookings.store'), [
                'booking_reference' => 'RESORT-CONFLICT-01',
                'guest_id' => $guest->id,
                'check_in_date' => '2026-11-20',
                'check_out_date' => '2026-11-22',
                'adults' => 2,
                'children' => 0,
                'booking_source' => 'website',
                'booking_status' => 'pending',
                'subtotal' => 1000,
                'discount' => 0,
                'tax' => 60,
                'total_amount' => 1060,
                'items' => [[
                    'bookable_unit_id' => $singleRoom->id,
                    'nightly_rate' => 500,
                    'adults' => 2,
                    'children' => 0,
                    'check_in_date' => '2026-11-20',
                    'check_out_date' => '2026-11-22',
                ]],
            ])
            ->assertRedirect(route('bookings.create'))
            ->assertSessionHasErrors(['items.0.bookable_unit_id']);
    }

    public function test_cancelled_booking_does_not_block_bookable_unit_availability(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();
        $roomA = Room::factory()->create(['code' => 'CNL-A']);
        $roomB = Room::factory()->create(['code' => 'CNL-B']);
        $poolGroup = $this->createBookableUnit('Pool Group', 'UNIT-CANCELLED-GROUP-TEST', 'room_group', 1200, 6, [$roomA, $roomB]);

        $cancelled = Booking::factory()->create([
            'booking_status' => 'cancelled',
            'check_in_date' => '2026-09-05',
            'check_out_date' => '2026-09-07',
        ]);

        $cancelled->bookingRoomItems()->create([
            'bookable_unit_id' => $poolGroup->id,
            'bookable_unit_name' => $poolGroup->name,
            'bookable_unit_code' => $poolGroup->code,
            'booking_type' => $poolGroup->booking_type,
            'included_rooms_snapshot' => [
                ['room_id' => $roomA->id, 'room_code' => $roomA->code, 'room_name' => $roomA->name],
                ['room_id' => $roomB->id, 'room_code' => $roomB->code, 'room_name' => $roomB->name],
            ],
            'room_id' => $roomA->id,
            'nightly_rate' => 1200,
            'adults' => 4,
            'children' => 0,
            'check_in_date' => '2026-09-05',
            'check_out_date' => '2026-09-07',
        ]);

        $this->actingAs($manager)
            ->post(route('bookings.store'), [
                'booking_reference' => 'CANCELLED-DOES-NOT-BLOCK',
                'guest_id' => $guest->id,
                'check_in_date' => '2026-09-05',
                'check_out_date' => '2026-09-07',
                'adults' => 4,
                'children' => 0,
                'booking_source' => 'website',
                'booking_status' => 'pending',
                'subtotal' => 2400,
                'discount' => 0,
                'tax' => 144,
                'total_amount' => 2544,
                'items' => [[
                    'bookable_unit_id' => $poolGroup->id,
                    'nightly_rate' => 1200,
                    'adults' => 4,
                    'children' => 0,
                    'check_in_date' => '2026-09-05',
                    'check_out_date' => '2026-09-07',
                ]],
            ])
            ->assertRedirect(route('bookings.index'));
    }

    public function test_bookable_unit_price_and_room_snapshot_remain_unchanged_after_source_updates(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();
        $roomA = Room::factory()->create(['code' => 'SNAP-A', 'name' => 'Snap Room A']);
        $roomB = Room::factory()->create(['code' => 'SNAP-B', 'name' => 'Snap Room B']);
        $unit = $this->createBookableUnit('Snapshot Unit', 'UNIT-SNAPSHOT-TEST', 'room_group', 1900, 8, [$roomA, $roomB]);

        $this->actingAs($manager)
            ->post(route('bookings.store'), [
                'booking_reference' => 'SNAPSHOT-BOOKING-01',
                'guest_id' => $guest->id,
                'check_in_date' => '2026-12-10',
                'check_out_date' => '2026-12-12',
                'adults' => 4,
                'children' => 1,
                'booking_source' => 'website',
                'booking_status' => 'confirmed',
                'subtotal' => 3800,
                'discount' => 0,
                'tax' => 228,
                'total_amount' => 4028,
                'items' => [[
                    'bookable_unit_id' => $unit->id,
                    'nightly_rate' => 1900,
                    'adults' => 4,
                    'children' => 1,
                    'check_in_date' => '2026-12-10',
                    'check_out_date' => '2026-12-12',
                ]],
            ])
            ->assertRedirect(route('bookings.index'));

        $booking = Booking::query()->where('booking_reference', 'SNAPSHOT-BOOKING-01')->firstOrFail();
        $item = $booking->bookingRoomItems()->firstOrFail();

        $unit->update(['base_nightly_rate' => 2500, 'name' => 'Snapshot Unit Updated']);
        $roomA->update(['code' => 'SNAP-A-NEW', 'name' => 'Snap Room A Updated']);

        $item->refresh();

        $this->assertSame('Snapshot Unit', $item->bookable_unit_name);
        $this->assertSame('UNIT-SNAPSHOT-TEST', $item->bookable_unit_code);
        $this->assertSame('room_group', $item->booking_type);
        $this->assertEquals(1900.0, (float) $item->nightly_rate);

        $snapshot = $item->included_rooms_snapshot;
        $this->assertIsArray($snapshot);
        $this->assertTrue(collect($snapshot)->contains(function (array $room): bool {
            return ($room['room_code'] ?? null) === 'SNAP-A' && ($room['room_name'] ?? null) === 'Snap Room A';
        }));
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

    public function test_bookings_index_can_render_filtered_calendar_view_for_a_selected_month(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        Booking::factory()->create([
            'booking_reference' => 'CAL-OVERLAP-001',
            'booking_status' => 'confirmed',
            'check_in_date' => '2026-07-30',
            'check_out_date' => '2026-08-02',
        ]);
        Booking::factory()->create([
            'booking_reference' => 'CAL-PENDING-001',
            'booking_status' => 'pending',
            'check_in_date' => '2026-08-15',
            'check_out_date' => '2026-08-18',
        ]);
        Booking::factory()->create([
            'booking_reference' => 'CAL-OUTSIDE-001',
            'booking_status' => 'confirmed',
            'check_in_date' => '2026-09-10',
            'check_out_date' => '2026-09-12',
        ]);

        $this->actingAs($manager)
            ->get(route('bookings.index', [
                'view' => 'calendar',
                'month' => '2026-08',
                'booking_status' => 'confirmed',
            ]))
            ->assertOk()
            ->assertSee('August 2026')
            ->assertSee('CAL-OVERLAP-001')
            ->assertDontSee('CAL-PENDING-001')
            ->assertDontSee('CAL-OUTSIDE-001')
            ->assertSee('name="view" value="calendar"', false)
            ->assertSee('name="month" value="2026-08"', false);

        $this->actingAs($manager)
            ->get(route('bookings.index', ['view' => 'calendar', 'month' => 'invalid']))
            ->assertOk()
            ->assertSee(now()->format('F Y'));
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

    /**
     * @param array<int, Room> $rooms
     */
    private function createBookableUnit(
        string $name,
        string $code,
        string $type,
        float $nightlyRate,
        int $maximumGuests,
        array $rooms,
    ): BookableUnit {
        $unit = BookableUnit::query()->create([
            'name' => $name,
            'code' => $code,
            'description' => null,
            'booking_type' => $type,
            'base_nightly_rate' => $nightlyRate,
            'maximum_guests' => $maximumGuests,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $unit->rooms()->sync(collect($rooms)->pluck('id')->all());

        return $unit;
    }
}
