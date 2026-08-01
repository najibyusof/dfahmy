<?php

namespace App\Policies;

use App\Models\Guest;
use App\Models\User;

class GuestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('guests.manage');
    }

    public function view(User $user, Guest $guest): bool
    {
        return $user->can('guests.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('guests.manage');
    }

    public function update(User $user, Guest $guest): bool
    {
        return $user->can('guests.manage');
    }

    public function delete(User $user, Guest $guest): bool
    {
        return $user->can('guests.manage');
    }
}
