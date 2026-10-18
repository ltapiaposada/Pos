<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('platform:create-admin {email : Correo de la cuenta de plataforma} {--name= : Nombre visible} {--password= : Contraseña; omítela para ingresarla sin mostrarla}', function (string $email): int {
    $password = $this->option('password') ?: $this->secret('Contraseña');

    if (! is_string($password) || $password === '') {
        $this->error('Debes indicar una contraseña.');
        return Command::FAILURE;
    }

    $user = User::updateOrCreate(
        ['email' => mb_strtolower($email)],
        ['name' => $this->option('name') ?: 'Administrador de plataforma', 'password' => Hash::make($password), 'email_verified_at' => now()]
    );

    $this->info("Cuenta de plataforma lista: {$user->email}");
    return Command::SUCCESS;
})->purpose('Crea o actualiza una cuenta con acceso a Administración');

Artisan::command('platform:refresh-subscriptions', function (): int {
    $expired = Subscription::query()
        ->where('status', 'active')
        ->whereNotNull('ends_at')
        ->whereDate('ends_at', '<', now()->toDateString())
        ->update(['status' => 'expired']);

    $this->info("Suscripciones vencidas actualizadas: {$expired}");
    return Command::SUCCESS;
})->purpose('Marca como vencidas las suscripciones cuya fecha de finalización ya pasó');
