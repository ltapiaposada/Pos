<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:doctor', function () {
    $errors = 0;
    $warns = 0;
    $isProduction = app()->environment('production');

    $ok = function (string $message) {
        $this->line("<fg=green>OK</>  {$message}");
    };
    $warn = function (string $message) use (&$warns) {
        $warns++;
        $this->line("<fg=yellow>WARN</> {$message}");
    };
    $fail = function (string $message) use (&$errors) {
        $errors++;
        $this->line("<fg=red>FAIL</> {$message}");
    };

    $this->info('App Doctor');
    $this->line('----------------------------------------');

    // App checks
    $appEnv = (string) config('app.env');
    $appKey = (string) config('app.key');
    $appDebug = (bool) config('app.debug');
    $sessionDriver = (string) config('session.driver');
    $sessionSecureCookie = (bool) config('session.secure');
    $mainConnection = (string) config('database.default');
    $mainDatabase = (string) config("database.connections.{$mainConnection}.database");
    $seedDemoData = filter_var(env('SEED_DEMO_DATA', app()->environment(['local', 'testing'])), FILTER_VALIDATE_BOOL);

    if ($appKey !== '') {
        $ok("APP_KEY configured ({$appEnv})");
    } else {
        $fail('APP_KEY is empty');
    }

    if ($appDebug) {
        $isProduction
            ? $fail('APP_DEBUG is enabled in production')
            : $warn('APP_DEBUG is enabled');
    } else {
        $ok('APP_DEBUG disabled');
    }

    if ($sessionSecureCookie) {
        $ok('SESSION_SECURE_COOKIE enabled');
    } else {
        $isProduction
            ? $fail('SESSION_SECURE_COOKIE disabled in production')
            : $warn('SESSION_SECURE_COOKIE disabled');
    }

    if ($seedDemoData) {
        $isProduction
            ? $fail('SEED_DEMO_DATA is enabled in production')
            : $warn('SEED_DEMO_DATA is enabled');
    } else {
        $ok('SEED_DEMO_DATA disabled');
    }

    // Main DB checks
    try {
        DB::connection($mainConnection)->select('select 1');
        $ok("Main DB connection works ({$mainConnection}: {$mainDatabase})");
    } catch (\Throwable $e) {
        $fail("Main DB connection failed: {$e->getMessage()}");
    }

    try {
        $usersCount = DB::connection($mainConnection)->table('users')->count();
        if ($usersCount > 0) {
            $ok("Users table has {$usersCount} user(s)");
        } else {
            $warn('Users table is empty');
        }
    } catch (\Throwable $e) {
        $fail("Cannot read users table: {$e->getMessage()}");
    }

    if ($sessionDriver === 'database') {
        try {
            $hasSessions = Schema::connection($mainConnection)->hasTable('sessions');
            if ($hasSessions) {
                $ok('Session driver is database and sessions table exists');
            } else {
                $fail('Session driver is database but sessions table does not exist');
            }
        } catch (\Throwable $e) {
            $fail("Session table check failed: {$e->getMessage()}");
        }
    } else {
        $warn("Session driver is {$sessionDriver} (not database)");
    }

    // Sales-readiness checks
    try {
        $companyCount = Company::query()->count();
        $activeCompanies = Company::query()->where('status', Company::STATUS_ACTIVE)->count();

        if ($companyCount === 0) {
            $fail('No companies found');
        } else {
            $ok("Companies found: {$companyCount}");
        }

        if ($activeCompanies === 0) {
            $fail('No active companies found');
        } else {
            $ok("Active companies: {$activeCompanies}");
        }
    } catch (\Throwable $e) {
        $fail("Cannot validate companies: {$e->getMessage()}");
    }

    try {
        $demoUsers = User::query()
            ->whereIn('email', ['admin@pos.test', 'supervisor@pos.test', 'cashier@pos.test'])
            ->pluck('email')
            ->all();

        if ($demoUsers === []) {
            $ok('No demo users found');
        } else {
            $message = 'Demo users still exist: '.implode(', ', $demoUsers);
            $isProduction ? $fail($message) : $warn($message);
        }
    } catch (\Throwable $e) {
        $fail("Cannot validate demo users: {$e->getMessage()}");
    }

    try {
        $realAdminCount = User::query()
            ->whereNotIn('email', ['admin@pos.test', 'supervisor@pos.test', 'cashier@pos.test'])
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))
            ->count();

        if ($realAdminCount > 0) {
            $ok("Real admin users found: {$realAdminCount}");
        } else {
            $isProduction
                ? $fail('No real admin user found outside demo accounts')
                : $warn('No real admin user found outside demo accounts');
        }
    } catch (\Throwable $e) {
        $fail("Cannot validate admin users: {$e->getMessage()}");
    }

    try {
        $companyWithoutDomain = Company::query()
            ->where('status', Company::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('domain')->orWhere('domain', '');
            })
            ->count();

        if ($companyWithoutDomain === 0) {
            $ok('Active companies have a domain configured');
        } else {
            $warn("Active companies without domain: {$companyWithoutDomain}");
        }
    } catch (\Throwable $e) {
        $fail("Cannot validate company domains: {$e->getMessage()}");
    }

    try {
        $businessSettings = Setting::withoutGlobalScopes()->where('key', 'business')->get();

        if ($businessSettings->isEmpty()) {
            $fail('No business settings found');
        } else {
            $ok("Business settings found: {$businessSettings->count()}");
        }

        $invalidBusinessSettings = $businessSettings->filter(function (Setting $setting) {
            $value = is_array($setting->value) ? $setting->value : [];

            return trim((string) ($value['name'] ?? '')) === ''
                || trim((string) ($value['currency'] ?? '')) === '';
        })->count();

        if ($invalidBusinessSettings === 0) {
            $ok('Business settings have name and currency');
        } else {
            $warn("Business settings incomplete: {$invalidBusinessSettings}");
        }

        $missingQrCount = $businessSettings->filter(function (Setting $setting) {
            $value = is_array($setting->value) ? $setting->value : [];

            return trim((string) ($value['payment_qr_url'] ?? '')) === '';
        })->count();

        if ($missingQrCount > 0) {
            $warn("Business settings without payment QR: {$missingQrCount}");
        } else {
            $ok('Payment QR configured in business settings');
        }
    } catch (\Throwable $e) {
        $fail("Cannot validate business settings: {$e->getMessage()}");
    }

    try {
        $storagePath = storage_path();
        if (is_dir($storagePath) && is_writable($storagePath)) {
            $ok('Storage path is writable');
        } else {
            $isProduction
                ? $fail('Storage path is not writable')
                : $warn('Storage path is not writable');
        }
    } catch (\Throwable $e) {
        $fail("Cannot validate storage path: {$e->getMessage()}");
    }

    // .env.testing safety checks
    $testingPath = base_path('.env.testing');
    if (! is_file($testingPath)) {
        $warn('.env.testing file does not exist');
    } else {
        $ok('.env.testing file exists');
        $lines = file($testingPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $map = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($trimmed, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $trimmed, 2);
            $map[trim($k)] = trim($v);
        }

        $testingConn = $map['DB_CONNECTION'] ?? '';
        $testingDb = $map['DB_DATABASE'] ?? '';

        if ($testingConn === '') {
            $fail('.env.testing missing DB_CONNECTION');
        } else {
            $ok(".env.testing DB_CONNECTION={$testingConn}");
        }

        if ($testingDb === '') {
            $fail('.env.testing missing DB_DATABASE');
        } else {
            $ok(".env.testing DB_DATABASE={$testingDb}");
        }

        if ($testingConn === 'sqlite' && $testingDb === ':memory:') {
            $ok('Testing DB uses in-memory sqlite');
        } elseif ($testingDb !== '' && $mainDatabase !== '' && $testingDb === $mainDatabase) {
            $fail('Testing DB is the same as main DB (unsafe)');
        } elseif ($testingDb !== '' && ! str_ends_with($testingDb, '_testing') && ! str_ends_with($testingDb, '_test') && $testingDb !== 'testing') {
            $warn('Testing DB name does not look isolated (_testing/_test)');
        } else {
            $ok('Testing DB is isolated from main DB');
        }
    }

    $this->line('----------------------------------------');
    $this->line("Summary: {$errors} fail(s), {$warns} warning(s)");

    return $errors > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Run safety and health checks for local app configuration');

Artisan::command('app:prepare-go-live
    {--company= : Company ID to update}
    {--domain= : Public domain for the company}
    {--business-name= : Commercial business name}
    {--currency= : Operational currency code}
    {--logo-url= : Public logo URL}
    {--payment-qr-url= : Public payment QR URL}
    {--shipping= : Flat ecommerce shipping amount}
    {--coupon=* : Coupon entries with CODE=VALUE}
    {--admin-name= : Real admin full name}
    {--admin-email= : Real admin email}
    {--admin-password= : Real admin password}
    {--disable-demo-users : Rename and lock seeded demo users}
', function () {
    $normalizeDomainOption = function (?string $value): ?string {
        if (! is_string($value)) {
            return null;
        }

        $domain = trim(mb_strtolower($value));
        if ($domain === '') {
            return null;
        }

        $candidate = preg_match('#^https?://#', $domain) === 1 ? $domain : 'https://'.$domain;
        $host = parse_url($candidate, PHP_URL_HOST);

        return is_string($host) && $host !== ''
            ? trim(mb_strtolower($host))
            : trim(mb_strtolower($domain), "/ \t\n\r\0\x0B");
    };

    $parseCouponOptions = function (array $entries): array {
        return collect($entries)
            ->mapWithKeys(function ($entry) {
                [$code, $percent] = array_pad(preg_split('/[=:]/', trim((string) $entry), 2), 2, null);
                $normalizedCode = strtoupper(trim((string) $code));
                $normalizedPercent = trim((string) $percent);

                if ($normalizedCode === '' || $normalizedPercent === '' || ! is_numeric($normalizedPercent)) {
                    return [];
                }

                return [$normalizedCode => round((float) $normalizedPercent, 2)];
            })
            ->all();
    };

    $companyId = $this->option('company');
    $company = Company::query()
        ->when($companyId, fn ($query) => $query->whereKey((int) $companyId))
        ->orderBy('id')
        ->first();

    if (! $company) {
        $this->error('No company found to prepare.');

        return self::FAILURE;
    }

    $this->info("Preparing company #{$company->id}: {$company->name}");

    if ($domain = $normalizeDomainOption($this->option('domain'))) {
        $company->update(['domain' => $domain]);
        $this->line("OK  Company domain updated to {$domain}");
    }

    $business = Setting::getValue('business', [], $company->id);
    $business = is_array($business) ? $business : [];

    if ($businessName = trim((string) $this->option('business-name'))) {
        $business['name'] = $businessName;
        $this->line("OK  Business name updated to {$businessName}");
    }

    if ($currency = strtoupper(trim((string) $this->option('currency')))) {
        $business['currency'] = $currency;
        $this->line("OK  Currency updated to {$currency}");
    }

    if (($shippingOption = $this->option('shipping')) !== null && $shippingOption !== '') {
        $business['ecommerce_flat_shipping'] = round((float) $shippingOption, 2);
        $this->line('OK  Ecommerce shipping updated');
    }

    if ($logoUrl = trim((string) $this->option('logo-url'))) {
        $business['logo_url'] = $logoUrl;
        $this->line('OK  Logo URL updated');
    }

    if ($paymentQrUrl = trim((string) $this->option('payment-qr-url'))) {
        $business['payment_qr_url'] = $paymentQrUrl;
        $this->line('OK  Payment QR URL updated');
    }

    $coupons = $parseCouponOptions((array) $this->option('coupon'));
    if ($coupons !== []) {
        $business['ecommerce_coupons'] = $coupons;
        $this->line('OK  Ecommerce coupons updated');
    }

    Setting::query()->updateOrCreate(
        ['company_id' => $company->id, 'key' => 'business'],
        ['value' => $business]
    );
    Setting::forgetValue('business', $company->id);

    $adminEmail = trim((string) $this->option('admin-email'));
    $adminPassword = (string) $this->option('admin-password');
    $adminName = trim((string) $this->option('admin-name'));

    if ($adminEmail !== '') {
        if ($adminPassword === '') {
            $this->error('The --admin-password option is required when --admin-email is provided.');

            return self::FAILURE;
        }

        $mainBranch = Branch::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->orderBy('id')
            ->first();

        if (! $mainBranch) {
            $mainBranch = Branch::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'name' => 'Sucursal Principal',
                'code' => 'PRN-'.$company->id,
                'address' => $company->address,
                'phone' => $company->phone,
            ]);
        }

        $admin = User::query()->firstOrNew(['email' => $adminEmail]);
        $admin->fill([
            'company_id' => $company->id,
            'branch_id' => $mainBranch->id,
            'name' => $adminName !== '' ? $adminName : ($admin->name ?: 'Administrador'),
            'password' => Hash::make($adminPassword),
        ]);
        $admin->save();
        $admin->syncRoles(['admin']);

        $this->line("OK  Admin user ready: {$adminEmail}");
    }

    if ((bool) $this->option('disable-demo-users')) {
        $disabled = 0;

        User::query()
            ->whereIn('email', ['admin@pos.test', 'supervisor@pos.test', 'cashier@pos.test'])
            ->get()
            ->each(function (User $user) use (&$disabled): void {
                $safeEmail = 'disabled+'.str_replace(['@', '.'], ['-at-', '-'], $user->email).'@invalid.local';
                $user->forceFill([
                    'email' => $safeEmail,
                    'name' => '[DEMO DISABLED] '.$user->name,
                    'password' => Hash::make(Str::random(32)),
                    'remember_token' => null,
                ])->save();

                $disabled++;
            });

        $this->line("OK  Demo users disabled: {$disabled}");
    }

    $this->info('Go-live preparation finished. Run php artisan app:doctor to verify the installation.');

    return self::SUCCESS;
})->purpose('Prepare a company for go-live by replacing demo defaults with commercial settings');
