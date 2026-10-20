<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = trim((string) env('ADMIN_INITIAL_EMAIL', ''));
        $password = (string) env('ADMIN_INITIAL_PASSWORD', '');

        if ($email === '' || $password === '') {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => trim((string) env('ADMIN_INITIAL_NAME', 'Administrador de plataforma')),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
    }
}
