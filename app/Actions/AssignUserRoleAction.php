<?php

namespace App\Actions;

use App\Models\RoleAssignmentAudit;
use App\Models\User;

class AssignUserRoleAction
{
    public function execute(User $actorUser, User $targetUser, string $roleName): void
    {
        $currentRole = $targetUser->getRoleNames()->first();

        if ($currentRole === $roleName) {
            return;
        }

        $targetUser->syncRoles([$roleName]);

        RoleAssignmentAudit::query()->create([
            'actor_user_id' => $actorUser->id,
            'target_user_id' => $targetUser->id,
            'from_role' => $currentRole,
            'to_role' => $roleName,
        ]);
    }
}
