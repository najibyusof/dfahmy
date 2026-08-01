<?php

namespace App\Services;

use App\Models\Room;

class RoomService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function create(array $validated): Room
    {
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        return Room::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Room $room, array $validated): void
    {
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $room->update($validated);
    }
}
