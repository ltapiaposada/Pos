<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['users', 'products', 'sales', 'customers'];

foreach ($tables as $table) {
    $count = Illuminate\Support\Facades\DB::table($table)->count();
    echo $table . '=' . $count . PHP_EOL;
}
