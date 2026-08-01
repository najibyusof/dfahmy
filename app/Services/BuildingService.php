<?php

namespace App\Services;

use App\Models\Building;

class BuildingService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function create(array $validated): Building
    {
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        return Building::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Building $building, array $validated): void
    {
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $building->update($validated);
    }
}
