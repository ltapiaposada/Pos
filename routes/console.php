<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:doctor', function () {
    $errors = 0;
    $warns = 0;

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
    $sessionDriver = (string) config('session.driver');
    $mainConnection = (string) config('database.default');
    $mainDatabase = (string) config("database.connections.{$mainConnection}.database");

    if ($appKey !== '') {
        $ok("APP_KEY configured ({$appEnv})");
    } else {
        $fail('APP_KEY is empty');
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

        if ($testingDb !== '' && $mainDatabase !== '' && $testingDb === $mainDatabase) {
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
