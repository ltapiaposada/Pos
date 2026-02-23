<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? '';
if ($email === '') {
    fwrite(STDERR, "Uso: php scripts/test-password-reset-broker.php correo@dominio.com\n");
    exit(1);
}

$user = App\Models\User::query()->where('email', $email)->first();
if (! $user) {
    fwrite(STDERR, "Usuario no encontrado\n");
    exit(1);
}

$token = Illuminate\Support\Facades\Password::broker()->createToken($user);

$status = Illuminate\Support\Facades\Password::reset(
    [
        'email' => $email,
        'token' => $token,
        'password' => 'password',
        'password_confirmation' => 'password',
    ],
    static function (App\Models\User $user): void {
        $user->forceFill([
            'password' => Illuminate\Support\Facades\Hash::make('password'),
            'remember_token' => Illuminate\Support\Str::random(60),
        ])->save();
    }
);

echo 'STATUS=' . $status . PHP_EOL;
echo 'OK=' . ($status === Illuminate\Support\Facades\Password::PASSWORD_RESET ? '1' : '0') . PHP_EOL;
