<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppDoctorCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_doctor_fails_on_empty_installation(): void
    {
        $this->artisan('app:doctor')
            ->expectsOutputToContain('FAIL')
            ->assertExitCode(1);
    }

    public function test_app_doctor_passes_for_clean_non_demo_installation(): void
    {
        $this->setEnvVar('SEED_DEMO_DATA', 'false');
        $this->setEnvVar('POS_INITIAL_ADMIN_NAME', 'Admin Cliente');
        $this->setEnvVar('POS_INITIAL_ADMIN_EMAIL', 'admin@cliente.test');
        $this->setEnvVar('POS_INITIAL_ADMIN_PASSWORD', 'Secret123!');

        try {
            $this->seed(DatabaseSeeder::class);

            $this->artisan('app:doctor')
                ->expectsOutputToContain('SEED_DEMO_DATA disabled')
                ->expectsOutputToContain('No demo users found')
                ->expectsOutputToContain('Real admin users found: 1')
                ->assertExitCode(0);
        } finally {
            $this->setEnvVar('SEED_DEMO_DATA', 'true');
            $this->setEnvVar('POS_INITIAL_ADMIN_NAME', null);
            $this->setEnvVar('POS_INITIAL_ADMIN_EMAIL', null);
            $this->setEnvVar('POS_INITIAL_ADMIN_PASSWORD', null);
        }
    }

    private function setEnvVar(string $key, ?string $value): void
    {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);

            return;
        }

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
