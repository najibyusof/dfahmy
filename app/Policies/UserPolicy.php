<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function updateRole(User $user, User $targetUser): bool
    {
        if (! $user->can('users.manage')) {
            return false;
        }

        // Prevent accidental lockout by disallowing self-demotion from role manager screen.
        return (int) $user->id !== (int) $targetUser->id;
    }

    public function createSystemUser(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function viewRoleMatrix(User $user): bool
    {
        return $user->can('users.manage');
    }

    /**
     * Determine whether the user can view the home dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->can('dashboard.view');
    }

    public function manageRooms(User $user): bool
    {
        return $user->can('rooms.manage');
    }

    public function manageBookings(User $user): bool
    {
        return $user->can('bookings.manage');
    }

    public function manageGuests(User $user): bool
    {
        return $user->can('guests.manage');
    }

    public function managePayments(User $user): bool
    {
        return $user->can('payments.manage');
    }

    public function manageCheckinCheckout(User $user): bool
    {
        return $user->can('checkin_checkout.manage');
    }

    public function manageHousekeeping(User $user): bool
    {
        return $user->can('housekeeping.manage');
    }

    public function manageMaintenance(User $user): bool
    {
        return $user->can('maintenance.manage');
    }

    public function viewReports(User $user): bool
    {
        return $user->can('reports.view');
    }

    public function viewAssignedHousekeepingTasks(User $user): bool
    {
        return $user->can('housekeeping.assigned.view');
    }

    public function updateAssignedHousekeepingTasks(User $user): bool
    {
        return $user->can('housekeeping.assigned.update');
    }
}
