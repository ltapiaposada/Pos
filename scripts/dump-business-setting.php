<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$setting = App\Models\Setting::query()->where('key', 'business')->first();

if (! $setting) {
    echo "missing\n";
    exit(0);
}

echo json_encode($setting->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
