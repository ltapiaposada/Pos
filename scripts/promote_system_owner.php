<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companyId = App\Models\Company::query()->value('id');
$branchId = App\Models\Branch::query()
    ->where('company_id', $companyId)
    ->orderBy('id')
    ->value('id') ?? App\Models\Branch::query()->orderBy('id')->value('id');

$user = App\Models\User::query()->firstOrNew([
    'email' => 'ldtapiaposada@gmail.com',
]);

if (! $user->exists) {
    $user->password = Illuminate\Support\Facades\Hash::make('password');
}

$user->name = $user->name ?: 'Luis Tapia';
$user->company_id = $companyId;
$user->branch_id = $branchId;
$user->save();

$role = Spatie\Permission\Models\Role::findOrCreate('system_owner');

Illuminate\Support\Facades\DB::table('model_has_roles')
    ->where('model_type', App\Models\User::class)
    ->where('model_id', $user->id)
    ->delete();

Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
    'role_id' => $role->id,
    'model_type' => App\Models\User::class,
    'model_id' => $user->id,
]);

app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

echo "PROMOTED\n";
