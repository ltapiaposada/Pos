<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        $roleIds = DB::table('roles')
            ->where('name', 'system_owner')
            ->pluck('id');

        if ($roleIds->isNotEmpty()) {
            DB::table('model_has_roles')->whereIn('role_id', $roleIds)->delete();
            DB::table('role_has_permissions')->whereIn('role_id', $roleIds)->delete();
            DB::table('roles')->whereIn('id', $roleIds)->delete();
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['manage_companies', 'manage_subscriptions'])
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }
    }

    public function down(): void
    {
        // No se recrean accesos de plataforma dentro del POS.
    }
};
