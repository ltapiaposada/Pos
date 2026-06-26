<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'manage_optometry_patients',
            'manage_optometry_records',
            'manage_optometry_orders',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (['admin', 'supervisor', 'system_owner'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

            if (! $role) {
                continue;
            }

            $role->givePermissionTo($permissions);
        }

        if (Role::where('name', 'cashier')->where('guard_name', 'web')->exists()) {
            Role::findByName('cashier', 'web')->givePermissionTo('manage_optometry_orders');
        }
    }

    public function down(): void
    {
        $permissions = [
            'manage_optometry_patients',
            'manage_optometry_records',
            'manage_optometry_orders',
        ];

        foreach (['admin', 'supervisor', 'cashier', 'system_owner'] as $roleName) {
            if (! Role::where('name', $roleName)->where('guard_name', 'web')->exists()) {
                continue;
            }

            Role::findByName($roleName, 'web')->revokePermissionTo($permissions);
        }

        Permission::whereIn('name', $permissions)->where('guard_name', 'web')->delete();
    }
};
