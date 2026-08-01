<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('bookings.manage');
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->can('bookings.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('bookings.manage');
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->can('bookings.manage');
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->can('bookings.manage');
    }
}
