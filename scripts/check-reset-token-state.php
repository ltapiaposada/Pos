<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? '';
if ($email === '') {
    fwrite(STDERR, "Uso: php scripts/check-reset-token-state.php correo@dominio.com\n");
    exit(1);
}

$row = Illuminate\Support\Facades\DB::table('password_reset_tokens')
    ->where('email', $email)
    ->first();

echo 'APP_URL=' . config('app.url') . PHP_EOL;
echo 'RESET_EXPIRE_MIN=' . config('auth.passwords.users.expire') . PHP_EOL;
echo 'NOW=' . now()->toDateTimeString() . PHP_EOL;

if (! $row) {
    echo "TOKEN_ROW=NONE\n";
    exit(0);
}

$createdAt = Illuminate\Support\Carbon::parse($row->created_at);
$ageMinutes = $createdAt->diffInMinutes(now());
$expiresAt = $createdAt->copy()->addMinutes((int) config('auth.passwords.users.expire'));

echo "TOKEN_ROW=FOUND\n";
echo 'CREATED_AT=' . $createdAt->toDateTimeString() . PHP_EOL;
echo 'AGE_MIN=' . $ageMinutes . PHP_EOL;
echo 'EXPIRES_AT=' . $expiresAt->toDateTimeString() . PHP_EOL;
echo 'IS_EXPIRED=' . (now()->greaterThan($expiresAt) ? '1' : '0') . PHP_EOL;
