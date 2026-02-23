<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KickoffSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $mainBranch = Branch::query()->updateOrCreate(
            ['code' => 'PRN'],
            [
                'name' => 'Sucursal Principal',
                'address' => 'Direccion principal',
                'phone' => '000-0000',
            ]
        );

        Category::query()->updateOrCreate(
            ['name' => 'Bebidas'],
            ['description' => 'Bebidas y refrescos']
        );
        Category::query()->updateOrCreate(
            ['name' => 'Snacks'],
            ['description' => 'Snacks y botanas']
        );

        Customer::query()->updateOrCreate(
            ['document' => '222222222222'],
            [
                'name' => 'Consumidor final',
                'email' => null,
                'phone' => null,
                'address' => null,
                'is_active' => true,
            ]
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name' => 'Administrador',
                'branch_id' => $mainBranch->id,
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles(['admin']);

        $supervisor = User::query()->updateOrCreate(
            ['email' => 'supervisor@pos.test'],
            [
                'name' => 'Supervisor',
                'branch_id' => $mainBranch->id,
                'password' => Hash::make('password'),
            ]
        );
        $supervisor->syncRoles(['supervisor']);

        $cashier = User::query()->updateOrCreate(
            ['email' => 'cashier@pos.test'],
            [
                'name' => 'Cajero',
                'branch_id' => $mainBranch->id,
                'password' => Hash::make('password'),
            ]
        );
        $cashier->syncRoles(['cashier']);
    }
}
