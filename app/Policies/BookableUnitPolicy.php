<?php

namespace App\Policies;

use App\Models\BookableUnit;
use App\Models\User;

class BookableUnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('bookings.manage');
    }

    public function view(User $user, BookableUnit $bookableUnit): bool
    {
        return $user->can('bookings.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('bookings.manage');
    }

    public function update(User $user, BookableUnit $bookableUnit): bool
    {
        return $user->can('bookings.manage');
    }

    public function delete(User $user, BookableUnit $bookableUnit): bool
    {
        return $user->can('bookings.manage');
    }
}
