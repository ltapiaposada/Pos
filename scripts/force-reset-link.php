<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? '';
if ($email === '') {
    fwrite(STDERR, "Uso: php scripts/force-reset-link.php correo@dominio.com\n");
    exit(1);
}

$user = App\Models\User::query()->where('email', $email)->first();
if (! $user) {
    fwrite(STDERR, "Usuario no encontrado\n");
    exit(1);
}

Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();

$token = Illuminate\Support\Facades\Password::broker()->createToken($user);
$url = route('password.reset', ['token' => $token, 'email' => $email]);

echo "RESET_URL={$url}\n";
echo "EXPIRES_MIN=" . config('auth.passwords.users.expire') . "\n";
