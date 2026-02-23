<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? '';
if ($email === '') {
    fwrite(STDERR, "Uso: php scripts/test-password-reset-flow.php correo@dominio.com\n");
    exit(1);
}

$user = App\Models\User::query()->where('email', $email)->first();
if (! $user) {
    $user = App\Models\User::query()->create([
        'name' => 'Usuario Recuperacion',
        'email' => $email,
        'password' => Illuminate\Support\Facades\Hash::make('password'),
    ]);
    echo "USER_CREATED=1\n";
} else {
    echo "USER_CREATED=0\n";
}

try {
    $status = Illuminate\Support\Facades\Password::sendResetLink(['email' => $email]);
    echo 'STATUS=' . $status . PHP_EOL;
    if ($status === Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
        echo "FLOW_OK=1\n";
        exit(0);
    }
    echo "FLOW_OK=0\n";
    exit(1);
} catch (Throwable $e) {
    echo "FLOW_OK=0\n";
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
