<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use App\Models\RoomBed;
use Illuminate\Database\Seeder;

class ResortStructureSeeder extends Seeder
{
    public function run(): void
    {
        $mainHouse = $this->upsertBuilding('Main House', 'MAIN', 2, 'Primary main villa complex.');
        $poolHouse = $this->upsertBuilding('Pool House', 'POOL', 2, 'Poolside villa building.');
        $tebingHouse = $this->upsertBuilding('Tebing House', 'TEBING', 1, 'Cliffside accommodation block.');
        $gardenSuite = $this->upsertBuilding('Garden Suite', 'GARDEN', 1, 'Premium garden-facing suite.');

        $this->seedRoom($mainHouse, 'Main House Room G1', 'MAIN-G1', 1, 'standard', 500, 2, ['queen_bed' => 1]);
        $this->seedRoom($mainHouse, 'Main House Room G2', 'MAIN-G2', 1, 'standard', 500, 2, ['queen_bed' => 1]);
        $this->seedRoom($mainHouse, 'Main House Room G3', 'MAIN-G3', 1, 'standard', 500, 2, ['queen_bed' => 1]);
        $this->seedRoom($mainHouse, 'Main House Room F1', 'MAIN-F1', 2, 'standard', 550, 3, ['queen_bed' => 1, 'sofa_bed' => 1]);
        $this->seedRoom($mainHouse, 'Main House Room F2', 'MAIN-F2', 2, 'standard', 550, 3, ['queen_bed' => 1, 'sofa_bed' => 1]);

        $this->seedRoom($poolHouse, 'Pool House Room G1', 'POOL-G1', 1, 'standard', 600, 3, ['queen_bed' => 1, 'sofa_bed' => 1]);
        $this->seedRoom($poolHouse, 'Pool House Room F1', 'POOL-F1', 2, 'standard', 620, 3, ['queen_bed' => 1, 'sofa_bed' => 1]);

        $this->seedRoom($tebingHouse, 'Tebing House Room 1', 'TEBING-1', 1, 'standard', 650, 2, ['queen_bed' => 1]);
        $this->seedRoom($tebingHouse, 'Tebing House Room 2', 'TEBING-2', 1, 'standard', 650, 2, ['queen_bed' => 1]);

        $this->seedRoom($gardenSuite, 'Garden Suite', 'GARDEN-1', 1, 'suite', 900, 6, ['queen_bed' => 2, 'sofa_bed' => 2]);
    }

    private function upsertBuilding(string $name, string $code, int $floors, string $description): Building
    {
        return Building::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => $description,
                'number_of_floors' => $floors,
                'is_active' => true,
            ],
        );
    }

    /**
     * @param array<string, int> $beds
     */
    private function seedRoom(
        Building $building,
        string $name,
        string $code,
        int $floor,
        string $roomType,
        int $rate,
        int $maxGuests,
        array $beds,
    ): void {
        $room = Room::query()->updateOrCreate(
            ['code' => $code],
            [
                'building_id' => $building->id,
                'name' => $name,
                'floor' => $floor,
                'room_type' => $roomType,
                'status' => 'available',
                'base_nightly_rate' => $rate,
                'maximum_guests' => $maxGuests,
                'notes' => null,
                'is_active' => true,
            ],
        );

        $keptTypes = [];

        foreach ($beds as $bedType => $quantity) {
            $keptTypes[] = $bedType;

            RoomBed::query()->updateOrCreate(
                ['room_id' => $room->id, 'bed_type' => $bedType],
                ['quantity' => $quantity],
            );
        }

        RoomBed::query()
            ->where('room_id', $room->id)
            ->whereNotIn('bed_type', $keptTypes)
            ->delete();
    }
}
