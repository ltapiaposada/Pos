<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = Company::query()->value('id');

        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name' => 'Admin POS',
                'company_id' => $companyId,
                'password' => Hash::make('password'),
                'branch_id' => 1,
            ]
        );
        $admin->assignRole('admin');

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@pos.test'],
            [
                'name' => 'Supervisor POS',
                'company_id' => $companyId,
                'password' => Hash::make('password'),
                'branch_id' => 1,
            ]
        );
        $supervisor->assignRole('supervisor');

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@pos.test'],
            [
                'name' => 'Cajero POS',
                'company_id' => $companyId,
                'password' => Hash::make('password'),
                'branch_id' => 1,
            ]
        );
        $cashier->assignRole('cashier');
    }
}
