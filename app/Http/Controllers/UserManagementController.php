<?php

namespace App\Http\Controllers;

use App\Actions\AssignUserRoleAction;
use App\Actions\ExportRoleAssignmentAuditCsvAction;
use App\Http\Requests\AssignUserRoleRequest;
use App\Http\Requests\RoleAuditFilterRequest;
use App\Models\User;
use App\Services\RoleAssignmentAuditQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(RoleAuditFilterRequest $request, RoleAssignmentAuditQueryService $auditQueryService): View
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->filters();
        $users = User::query()->latest()->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => ['Super Admin', 'Manager', 'Receptionist', 'Housekeeper'],
            'auditLogs' => $auditQueryService->query($filters)
                ->paginate($filters['per_page'])
                ->withQueryString(),
            'filters' => $filters,
            'sortOptions' => [
                'created_at_desc' => 'Newest first',
                'created_at_asc' => 'Oldest first',
                'actor_asc' => 'Actor A-Z',
                'actor_desc' => 'Actor Z-A',
                'target_asc' => 'Target A-Z',
                'target_desc' => 'Target Z-A',
                'from_role_asc' => 'From role A-Z',
                'from_role_desc' => 'From role Z-A',
                'to_role_asc' => 'To role A-Z',
                'to_role_desc' => 'To role Z-A',
            ],
            'perPageOptions' => [10, 25, 50, 100],
        ]);
    }

    public function updateRole(AssignUserRoleRequest $request, User $user, AssignUserRoleAction $assignUserRoleAction): RedirectResponse
    {
        $this->authorize('updateRole', $user);

        if ((int) $request->user()->id === (int) $user->id) {
            abort(403);
        }

        $assignUserRoleAction->execute($request->user(), $user, $request->validated('role'));

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'user-role-updated');
    }

    public function exportAuditCsv(
        RoleAuditFilterRequest $request,
        ExportRoleAssignmentAuditCsvAction $exportRoleAssignmentAuditCsvAction,
        RoleAssignmentAuditQueryService $auditQueryService,
    ): StreamedResponse {
        $this->authorize('viewAny', User::class);

        return $exportRoleAssignmentAuditCsvAction->execute($request->filters(), $auditQueryService);
    }
}
