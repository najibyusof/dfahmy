<?php

namespace Database\Seeders;

use App\Models\BookableUnit;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class BookableUnitSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::query()
            ->with('building:id,code,name')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $this->seedIndividualRoomUnits($rooms);

        $poolHouseRooms = $this->roomsInBuilding($rooms, 'POOL');
        $mainGroundRooms = $this->roomsInBuildingAndFloor($rooms, 'MAIN', 1);
        $mainFirstRooms = $this->roomsInBuildingAndFloor($rooms, 'MAIN', 2);
        $mainHouseRooms = $this->roomsInBuilding($rooms, 'MAIN');
        $tebingRooms = $this->roomsInBuilding($rooms, 'TEBING');
        $gardenSuiteRooms = $this->roomsInBuilding($rooms, 'GARDEN');

        $this->upsertUnit('All Pool House Rooms', 'UNIT-POOL-ALL', 'room_group', 2300, 10, 200, $poolHouseRooms, 'All active rooms in Pool House.');
        $this->upsertUnit('Main House Ground Floor', 'UNIT-MAIN-GROUND', 'floor', 1500, 6, 210, $mainGroundRooms, 'All active Main House ground floor rooms.');
        $this->upsertUnit('Main House First Floor', 'UNIT-MAIN-FIRST', 'floor', 1700, 6, 220, $mainFirstRooms, 'All active Main House first floor rooms.');
        $this->upsertUnit('Entire Main House', 'UNIT-MAIN-ALL', 'building', 3000, 12, 230, $mainHouseRooms, 'All active rooms in Main House.');
        $this->upsertUnit('Entire Tebing House', 'UNIT-TEBING-ALL', 'building', 1800, 6, 240, $tebingRooms, 'All active rooms in Tebing House.');
        $this->upsertUnit('Garden Suite', 'UNIT-GARDEN-SUITE', 'building', 1200, 6, 250, $gardenSuiteRooms, 'Garden Suite inventory.');
        $this->upsertUnit('Whole Resort / All Rooms', 'UNIT-RESORT-ALL', 'whole_resort', 7600, 30, 300, $rooms, 'All active rooms across the entire resort.');
    }

    /**
     * @param Collection<int, Room> $rooms
     */
    private function seedIndividualRoomUnits(Collection $rooms): void
    {
        $sort = 1;

        foreach ($rooms as $room) {
            $name = 'Room: ' . $room->code . ' - ' . $room->name;
            $code = 'UNIT-ROOM-' . strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $room->code));

            $this->upsertUnit(
                $name,
                $code,
                'room',
                (float) $room->base_nightly_rate,
                (int) $room->maximum_guests,
                $sort,
                collect([$room]),
                'Individual room booking unit.'
            );

            $sort++;
        }
    }

    /**
     * @param Collection<int, Room> $rooms
     * @return Collection<int, Room>
     */
    private function roomsInBuilding(Collection $rooms, string $buildingCode): Collection
    {
        return $rooms->filter(function (Room $room) use ($buildingCode): bool {
            return strtoupper((string) $room->building?->code) === strtoupper($buildingCode);
        })->values();
    }

    /**
     * @param Collection<int, Room> $rooms
     * @return Collection<int, Room>
     */
    private function roomsInBuildingAndFloor(Collection $rooms, string $buildingCode, int $floor): Collection
    {
        return $rooms->filter(function (Room $room) use ($buildingCode, $floor): bool {
            return strtoupper((string) $room->building?->code) === strtoupper($buildingCode)
                && (int) $room->floor === $floor;
        })->values();
    }

    /**
     * @param Collection<int, Room> $rooms
     */
    private function upsertUnit(
        string $name,
        string $code,
        string $bookingType,
        float $rate,
        int $maximumGuests,
        int $sortOrder,
        Collection $rooms,
        string $description,
    ): void {
        if ($rooms->isEmpty()) {
            return;
        }

        $unit = BookableUnit::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => $description,
                'booking_type' => $bookingType,
                'base_nightly_rate' => $rate,
                'maximum_guests' => $maximumGuests,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]
        );

        $unit->rooms()->sync($rooms->pluck('id')->all());
    }
}
