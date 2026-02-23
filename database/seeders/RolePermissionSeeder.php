<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_users',
            'manage_settings',
            'manage_products',
            'manage_categories',
            'manage_branches',
            'manage_customers',
            'manage_inventory',
            'view_reports',
            'open_cash_register',
            'close_cash_register',
            'record_cash_movement',
            'create_sale',
            'apply_discount',
            'apply_high_discount',
            'void_sale',
            'process_return',
            'manage_purchases',
            'manage_accounting',
            'manage_ecommerce_orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $customer = Role::firstOrCreate(['name' => 'customer']);

        $admin->syncPermissions($permissions);
        $supervisor->syncPermissions([
            'manage_products',
            'manage_categories',
            'manage_branches',
            'manage_customers',
            'manage_inventory',
            'view_reports',
            'open_cash_register',
            'close_cash_register',
            'record_cash_movement',
            'create_sale',
            'apply_discount',
            'apply_high_discount',
            'void_sale',
            'process_return',
            'manage_purchases',
            'manage_accounting',
            'manage_ecommerce_orders',
        ]);
        $cashier->syncPermissions([
            'open_cash_register',
            'close_cash_register',
            'record_cash_movement',
            'create_sale',
            'apply_discount',
        ]);
        $customer->syncPermissions([]);
    }
}
