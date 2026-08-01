<?php

namespace App\Services;

use App\Models\RoleAssignmentAudit;
use Illuminate\Database\Eloquent\Builder;

class RoleAssignmentAuditQueryService
{
    /**
     * @param array<string, mixed> $filters
     */
    public function query(array $filters): Builder
    {
        $query = RoleAssignmentAudit::query()
            ->with(['actor:id,name,email', 'target:id,name,email'])
            ->when($filters['actor'] ?? null, function (Builder $query, string $actor): void {
                $query->whereHas('actor', function (Builder $actorQuery) use ($actor): void {
                    $actorQuery->where('name', 'like', '%' . $actor . '%')
                        ->orWhere('email', 'like', '%' . $actor . '%');
                });
            })
            ->when($filters['target'] ?? null, function (Builder $query, string $target): void {
                $query->whereHas('target', function (Builder $targetQuery) use ($target): void {
                    $targetQuery->where('name', 'like', '%' . $target . '%')
                        ->orWhere('email', 'like', '%' . $target . '%');
                });
            })
            ->when($filters['from_role'] ?? null, static function (Builder $query, string $fromRole): void {
                $query->where('from_role', $fromRole);
            })
            ->when($filters['to_role'] ?? null, static function (Builder $query, string $toRole): void {
                $query->where('to_role', $toRole);
            })
            ->when($filters['from_date'] ?? null, static function (Builder $query, string $fromDate): void {
                $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($filters['to_date'] ?? null, static function (Builder $query, string $toDate): void {
                $query->whereDate('created_at', '<=', $toDate);
            });

        return $this->applySort($query, (string) ($filters['sort'] ?? 'created_at_desc'));
    }

    private function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'created_at_asc' => $query
                ->orderBy('role_assignment_audits.created_at')
                ->orderBy('role_assignment_audits.id'),
            'actor_asc' => $query
                ->leftJoin('users as actor_users', 'actor_users.id', '=', 'role_assignment_audits.actor_user_id')
                ->select('role_assignment_audits.*')
                ->orderBy('actor_users.name')
                ->orderBy('role_assignment_audits.id'),
            'actor_desc' => $query
                ->leftJoin('users as actor_users', 'actor_users.id', '=', 'role_assignment_audits.actor_user_id')
                ->select('role_assignment_audits.*')
                ->orderByDesc('actor_users.name')
                ->orderByDesc('role_assignment_audits.id'),
            'target_asc' => $query
                ->leftJoin('users as target_users', 'target_users.id', '=', 'role_assignment_audits.target_user_id')
                ->select('role_assignment_audits.*')
                ->orderBy('target_users.name')
                ->orderBy('role_assignment_audits.id'),
            'target_desc' => $query
                ->leftJoin('users as target_users', 'target_users.id', '=', 'role_assignment_audits.target_user_id')
                ->select('role_assignment_audits.*')
                ->orderByDesc('target_users.name')
                ->orderByDesc('role_assignment_audits.id'),
            'from_role_asc' => $query
                ->orderBy('role_assignment_audits.from_role')
                ->orderBy('role_assignment_audits.id'),
            'from_role_desc' => $query
                ->orderByDesc('role_assignment_audits.from_role')
                ->orderByDesc('role_assignment_audits.id'),
            'to_role_asc' => $query
                ->orderBy('role_assignment_audits.to_role')
                ->orderBy('role_assignment_audits.id'),
            'to_role_desc' => $query
                ->orderByDesc('role_assignment_audits.to_role')
                ->orderByDesc('role_assignment_audits.id'),
            default => $query
                ->orderByDesc('role_assignment_audits.created_at')
                ->orderByDesc('role_assignment_audits.id'),
        };
    }
}
