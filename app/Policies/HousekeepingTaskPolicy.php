<?php

namespace App\Policies;

use App\Models\HousekeepingTask;
use App\Models\User;

class HousekeepingTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('housekeeping.manage') || $user->can('housekeeping.assigned.view');
    }

    public function create(User $user): bool
    {
        return $user->can('housekeeping.manage');
    }

    public function view(User $user, HousekeepingTask $housekeepingTask): bool
    {
        return $user->can('housekeeping.manage')
            || ((int) $housekeepingTask->assigned_to_user_id === (int) $user->id && $user->can('housekeeping.assigned.view'));
    }

    public function update(User $user, HousekeepingTask $housekeepingTask): bool
    {
        if ($user->can('housekeeping.manage')) {
            return true;
        }

        return $user->can('housekeeping.assigned.update')
            && (int) $housekeepingTask->assigned_to_user_id === (int) $user->id;
    }

    public function delete(User $user, HousekeepingTask $housekeepingTask): bool
    {
        return $user->can('housekeeping.manage');
    }
}
