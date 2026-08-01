<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleMatrixController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewRoleMatrix', User::class);

        $permissions = Permission::query()
            ->orderBy('name')
            ->pluck('name');

        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get();

        return view('admin.roles-matrix.index', [
            'permissions' => $permissions,
            'roles' => $roles,
        ]);
    }
}
