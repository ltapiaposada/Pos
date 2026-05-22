<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::query()->where('email', 'ldtapiaposada@gmail.com')->first();

if (! $user) {
    echo "NOT_FOUND\n";
    exit(0);
}

echo json_encode([
    'id' => $user->id,
    'email' => $user->email,
    'name' => $user->name,
    'company_id' => $user->company_id,
    'branch_id' => $user->branch_id,
    'roles' => $user->getRoleNames()->values()->all(),
    'is_system_admin' => method_exists($user, 'isSystemAdmin') ? $user->isSystemAdmin() : false,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
