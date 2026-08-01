<?php

namespace App\Policies;

use App\Models\Building;
use App\Models\User;

class BuildingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('rooms.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('rooms.manage');
    }

    public function update(User $user, Building $building): bool
    {
        return $user->can('rooms.manage');
    }

    public function delete(User $user, Building $building): bool
    {
        return $user->can('rooms.manage');
    }

    public function restore(User $user, Building $building): bool
    {
        return $user->can('rooms.manage');
    }
}
