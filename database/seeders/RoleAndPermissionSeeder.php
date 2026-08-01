<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'dashboard.view',
        'users.manage',
        'rooms.manage',
        'bookings.manage',
        'guests.manage',
        'payments.manage',
        'payments.overpay.override',
        'checkin_checkout.manage',
        'housekeeping.manage',
        'housekeeping.assigned.view',
        'housekeeping.assigned.update',
        'maintenance.manage',
        'reports.view',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $allPermissions = Permission::query()->pluck('name')->all();

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);

        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'dashboard.view',
            'rooms.manage',
            'bookings.manage',
            'guests.manage',
            'payments.manage',
            'payments.overpay.override',
            'housekeeping.manage',
            'maintenance.manage',
            'reports.view',
        ]);

        $receptionist = Role::firstOrCreate(['name' => 'Receptionist', 'guard_name' => 'web']);
        $receptionist->syncPermissions([
            'dashboard.view',
            'guests.manage',
            'bookings.manage',
            'checkin_checkout.manage',
            'payments.manage',
        ]);

        $housekeeper = Role::firstOrCreate(['name' => 'Housekeeper', 'guard_name' => 'web']);
        $housekeeper->syncPermissions([
            'dashboard.view',
            'housekeeping.assigned.view',
            'housekeeping.assigned.update',
        ]);
    }
}
