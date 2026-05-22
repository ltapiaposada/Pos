<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KickoffSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $company = Company::query()->first();

        $mainBranch = Branch::query()->updateOrCreate(
            ['code' => 'PRN'],
            [
                'company_id' => $company?->id,
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
                'company_id' => $company?->id,
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
                'company_id' => $company?->id,
                'branch_id' => $mainBranch->id,
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles(['admin']);

        $supervisor = User::query()->updateOrCreate(
            ['email' => 'supervisor@pos.test'],
            [
                'name' => 'Supervisor',
                'company_id' => $company?->id,
                'branch_id' => $mainBranch->id,
                'password' => Hash::make('password'),
            ]
        );
        $supervisor->syncRoles(['supervisor']);

        $cashier = User::query()->updateOrCreate(
            ['email' => 'cashier@pos.test'],
            [
                'name' => 'Cajero',
                'company_id' => $company?->id,
                'branch_id' => $mainBranch->id,
                'password' => Hash::make('password'),
            ]
        );
        $cashier->syncRoles(['cashier']);

        $systemOwner = User::query()->firstOrNew([
            'email' => 'ldtapiaposada@gmail.com',
        ]);
        $systemOwner->fill([
            'name' => $systemOwner->name ?: 'Luis Tapia',
            'company_id' => $company?->id,
            'branch_id' => $mainBranch->id,
        ]);

        if (! $systemOwner->exists) {
            $systemOwner->password = Hash::make('password');
        }

        $systemOwner->save();
        $systemOwner->syncRoles(['system_owner']);
    }
}
