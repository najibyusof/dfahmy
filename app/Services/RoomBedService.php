<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomBed;

class RoomBedService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function sync(Room $room, array $validated): void
    {
        $beds = [
            'queen_bed' => (int) $validated['queen_bed_quantity'],
            'sofa_bed' => (int) $validated['sofa_bed_quantity'],
        ];

        foreach ($beds as $bedType => $quantity) {
            if ($quantity > 0) {
                RoomBed::query()->updateOrCreate(
                    ['room_id' => $room->id, 'bed_type' => $bedType],
                    ['quantity' => $quantity],
                );

                continue;
            }

            RoomBed::query()
                ->where('room_id', $room->id)
                ->where('bed_type', $bedType)
                ->delete();
        }
    }
}
