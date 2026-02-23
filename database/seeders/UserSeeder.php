<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name' => 'Admin POS',
                'password' => Hash::make('password'),
                'branch_id' => 1,
            ]
        );
        $admin->assignRole('admin');

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@pos.test'],
            [
                'name' => 'Supervisor POS',
                'password' => Hash::make('password'),
                'branch_id' => 1,
            ]
        );
        $supervisor->assignRole('supervisor');

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@pos.test'],
            [
                'name' => 'Cajero POS',
                'password' => Hash::make('password'),
                'branch_id' => 1,
            ]
        );
        $cashier->assignRole('cashier');
    }
}
