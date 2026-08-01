<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Room;
use App\Models\RoomBed;
use App\Models\User;
use Database\Seeders\ResortStructureSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RoomConfigurationModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_receptionist_cannot_access_building_room_and_bed_configuration_modules(): void
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $this->actingAs($receptionist)->get(route('buildings.index'))->assertForbidden();
        $this->actingAs($receptionist)->get(route('rooms.index'))->assertForbidden();
        $this->actingAs($receptionist)->get(route('room-bed-configurations.index'))->assertForbidden();
        $this->actingAs($receptionist)->get(route('rooms.import.form'))->assertForbidden();
        $this->actingAs($receptionist)->get(route('rooms.export'))->assertForbidden();
        $this->actingAs($receptionist)->get(route('rooms.availability-calendar'))->assertForbidden();
    }

    public function test_manager_can_create_update_soft_delete_and_restore_a_building(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $this->actingAs($manager)->post(route('buildings.store'), [
            'name' => 'Test Building',
            'code' => 'TB1',
            'description' => 'Test description',
            'number_of_floors' => 3,
            'is_active' => '1',
        ])->assertRedirect(route('buildings.index'));

        $building = Building::query()->where('code', 'TB1')->firstOrFail();

        $this->actingAs($manager)->patch(route('buildings.update', $building), [
            'name' => 'Updated Building',
            'code' => 'TB1',
            'description' => 'Updated description',
            'number_of_floors' => 4,
            'is_active' => '0',
        ])->assertRedirect(route('buildings.index'));

        $this->assertDatabaseHas('buildings', [
            'id' => $building->id,
            'name' => 'Updated Building',
            'number_of_floors' => 4,
            'is_active' => 0,
        ]);

        $this->actingAs($manager)->delete(route('buildings.destroy', $building))
            ->assertRedirect(route('buildings.index'));

        $this->assertSoftDeleted('buildings', ['id' => $building->id]);

        $this->actingAs($manager)->post(route('buildings.restore', $building->id))
            ->assertRedirect(route('buildings.index', ['lifecycle' => 'archived']));

        $this->assertDatabaseHas('buildings', [
            'id' => $building->id,
            'deleted_at' => null,
        ]);
    }

    public function test_manager_can_create_and_filter_rooms_and_validation_rejects_invalid_status(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $building = Building::factory()->create(['name' => 'Main House', 'code' => 'MAIN']);

        $this->actingAs($manager)->post(route('rooms.store'), [
            'building_id' => $building->id,
            'name' => 'Main Room A',
            'code' => 'MAIN-A',
            'floor' => 1,
            'room_type' => 'standard',
            'status' => 'available',
            'base_nightly_rate' => 500,
            'maximum_guests' => 2,
            'notes' => 'Sea view',
            'is_active' => '1',
        ])->assertRedirect(route('rooms.index'));

        $room = Room::query()->where('code', 'MAIN-A')->firstOrFail();

        $this->actingAs($manager)->patch(route('rooms.update', $room), [
            'building_id' => $building->id,
            'name' => 'Main Room A Updated',
            'code' => 'MAIN-A',
            'floor' => 2,
            'room_type' => 'suite',
            'status' => 'reserved',
            'base_nightly_rate' => 650,
            'maximum_guests' => 3,
            'notes' => 'Updated notes',
            'is_active' => '1',
        ])->assertRedirect(route('rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'reserved',
            'maximum_guests' => 3,
        ]);

        $this->actingAs($manager)->get(route('rooms.index', ['search' => 'MAIN-A', 'status' => 'reserved']))
            ->assertOk()
            ->assertSee('Main Room A Updated');

        $this->actingAs($manager)
            ->from(route('rooms.create'))
            ->post(route('rooms.store'), [
                'building_id' => $building->id,
                'name' => 'Invalid Room',
                'code' => 'MAIN-B',
                'floor' => 1,
                'room_type' => 'standard',
                'status' => 'invalid_status',
                'base_nightly_rate' => 300,
                'maximum_guests' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect(route('rooms.create'))
            ->assertSessionHasErrors(['status']);
    }

    public function test_manager_can_update_room_bed_configuration(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $room = Room::factory()->create();

        $this->actingAs($manager)->patch(route('room-bed-configurations.update', $room), [
            'queen_bed_quantity' => 2,
            'sofa_bed_quantity' => 1,
        ])->assertRedirect(route('room-bed-configurations.index'));

        $this->assertDatabaseHas('room_beds', [
            'room_id' => $room->id,
            'bed_type' => 'queen_bed',
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('room_beds', [
            'room_id' => $room->id,
            'bed_type' => 'sofa_bed',
            'quantity' => 1,
        ]);
    }

    public function test_manager_can_soft_delete_and_restore_a_room(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $room = Room::factory()->create([
            'name' => 'Soft Delete Room',
            'code' => 'SOFT-DEL',
        ]);

        $this->actingAs($manager)
            ->delete(route('rooms.destroy', $room))
            ->assertRedirect(route('rooms.index'));

        $this->assertSoftDeleted('rooms', ['id' => $room->id]);

        $this->actingAs($manager)
            ->get(route('rooms.index'))
            ->assertDontSee('Soft Delete Room');

        $this->actingAs($manager)
            ->get(route('rooms.index', ['lifecycle' => 'archived']))
            ->assertSee('Soft Delete Room');

        $this->actingAs($manager)
            ->post(route('rooms.restore', $room->id))
            ->assertRedirect(route('rooms.index', ['lifecycle' => 'archived']));

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'deleted_at' => null,
        ]);
    }

    public function test_resort_structure_seeder_creates_expected_buildings_rooms_and_beds(): void
    {
        $this->seed(ResortStructureSeeder::class);

        $this->assertEquals(4, Building::query()->count());
        $this->assertEquals(10, Room::query()->count());

        $mainHouse = Building::query()->where('code', 'MAIN')->firstOrFail();
        $poolHouse = Building::query()->where('code', 'POOL')->firstOrFail();
        $tebingHouse = Building::query()->where('code', 'TEBING')->firstOrFail();
        $gardenSuite = Building::query()->where('code', 'GARDEN')->firstOrFail();

        $this->assertEquals(5, $mainHouse->rooms()->count());
        $this->assertEquals(2, $poolHouse->rooms()->count());
        $this->assertEquals(2, $tebingHouse->rooms()->count());
        $this->assertEquals(1, $gardenSuite->rooms()->count());

        $mainFirstFloorRooms = Room::query()->where('building_id', $mainHouse->id)->where('floor', 2)->get();
        foreach ($mainFirstFloorRooms as $room) {
            $this->assertEquals(1, (int) $room->beds()->where('bed_type', 'queen_bed')->value('quantity'));
            $this->assertEquals(1, (int) $room->beds()->where('bed_type', 'sofa_bed')->value('quantity'));
        }

        $gardenSuiteRoom = Room::query()->where('building_id', $gardenSuite->id)->firstOrFail();
        $this->assertEquals(2, (int) $gardenSuiteRoom->beds()->where('bed_type', 'queen_bed')->value('quantity'));
        $this->assertEquals(2, (int) $gardenSuiteRoom->beds()->where('bed_type', 'sofa_bed')->value('quantity'));

        $this->assertTrue(RoomBed::query()->count() >= 10);
    }

    public function test_manager_can_export_rooms_csv(): void
    {
        $this->seed(ResortStructureSeeder::class);

        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $response = $this->actingAs($manager)->get(route('rooms.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('building_code,name,code,floor,room_type,status,base_nightly_rate,maximum_guests,notes,is_active,queen_bed_quantity,sofa_bed_quantity', $csv);
        $this->assertStringContainsString('MAIN', $csv);
    }

    public function test_manager_can_import_rooms_csv(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        Building::factory()->create(['name' => 'Main House', 'code' => 'MAIN']);

        $csv = "building_code,name,code,floor,room_type,status,base_nightly_rate,maximum_guests,notes,is_active,queen_bed_quantity,sofa_bed_quantity\n" .
            "MAIN,Imported Room,IMP-101,1,standard,available,450,2,Imported from CSV,1,1,1\n";

        $file = UploadedFile::fake()->createWithContent('rooms.csv', $csv);

        $this->actingAs($manager)
            ->post(route('rooms.import'), ['csv_file' => $file])
            ->assertRedirect(route('rooms.index'));

        $room = Room::query()->where('code', 'IMP-101')->firstOrFail();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Imported Room',
            'status' => 'available',
            'maximum_guests' => 2,
        ]);

        $this->assertDatabaseHas('room_beds', [
            'room_id' => $room->id,
            'bed_type' => 'queen_bed',
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('room_beds', [
            'room_id' => $room->id,
            'bed_type' => 'sofa_bed',
            'quantity' => 1,
        ]);
    }

    public function test_import_rooms_csv_rejects_invalid_status(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        Building::factory()->create(['name' => 'Main House', 'code' => 'MAIN']);

        $csv = "building_code,name,code,floor,room_type,status,base_nightly_rate,maximum_guests,notes,is_active,queen_bed_quantity,sofa_bed_quantity\n" .
            "MAIN,Imported Room,IMP-102,1,standard,bad_status,450,2,Imported from CSV,1,1,1\n";

        $file = UploadedFile::fake()->createWithContent('rooms.csv', $csv);

        $this->actingAs($manager)
            ->from(route('rooms.import.form'))
            ->post(route('rooms.import'), ['csv_file' => $file])
            ->assertRedirect(route('rooms.import.form'))
            ->assertSessionHasErrors(['csv_file']);
    }

    public function test_manager_can_view_room_availability_calendar_and_filter_by_building(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $main = Building::factory()->create(['name' => 'Main House', 'code' => 'MAIN']);
        $pool = Building::factory()->create(['name' => 'Pool House', 'code' => 'POOL']);

        Room::factory()->create([
            'building_id' => $main->id,
            'name' => 'Main A',
            'code' => 'MAIN-A',
            'status' => 'available',
            'is_active' => true,
        ]);

        Room::factory()->create([
            'building_id' => $pool->id,
            'name' => 'Pool A',
            'code' => 'POOL-A',
            'status' => 'reserved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)
            ->get(route('rooms.availability-calendar', [
                'building_id' => $main->id,
                'on_date' => '2026-08-01',
            ]));

        $response->assertOk();
        $response->assertSee('Room Availability Calendar');
        $response->assertSee('Main A');
        $response->assertDontSee('Pool A');
        $response->assertSee('available');
    }
}
