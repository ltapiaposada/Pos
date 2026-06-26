<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrepareGoLiveCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepare_go_live_updates_business_data_creates_admin_and_disables_demo_users(): void
    {
        $this->setEnvVar('SEED_DEMO_DATA', 'true');
        $this->seed(DatabaseSeeder::class);

        $company = Company::query()->firstOrFail();

        $this->artisan('app:prepare-go-live', [
                '--company' => $company->id,
                '--domain' => 'tienda-cliente.test',
                '--business-name' => 'Tienda Cliente',
                '--currency' => 'COP',
                '--shipping' => '14.75',
                '--coupon' => ['CLIENTE10=10', 'MAYORISTA5=5'],
                '--payment-qr-url' => 'https://cdn.example.com/qr.png',
                '--logo-url' => 'https://cdn.example.com/logo.png',
                '--admin-name' => 'Admin Real',
                '--admin-email' => 'admin@cliente.test',
                '--admin-password' => 'Secret123!',
                '--disable-demo-users' => true,
            ])
            ->assertExitCode(0);

        $company->refresh();
        $this->assertSame('tienda-cliente.test', $company->domain);

        $business = Setting::getValue('business', [], $company->id);
        $this->assertSame('Tienda Cliente', $business['name']);
        $this->assertSame('COP', $business['currency']);
        $this->assertSame(14.75, (float) $business['ecommerce_flat_shipping']);
        $this->assertSame('https://cdn.example.com/qr.png', $business['payment_qr_url']);
        $this->assertSame('https://cdn.example.com/logo.png', $business['logo_url']);
        $this->assertSame(['CLIENTE10' => 10, 'MAYORISTA5' => 5], $business['ecommerce_coupons']);

        $admin = User::query()->where('email', 'admin@cliente.test')->firstOrFail();
        $this->assertSame('Admin Real', $admin->name);
        $this->assertTrue($admin->hasRole('admin'));

        $this->assertDatabaseMissing('users', ['email' => 'admin@pos.test']);
        $this->assertDatabaseMissing('users', ['email' => 'supervisor@pos.test']);
        $this->assertDatabaseMissing('users', ['email' => 'cashier@pos.test']);
        $this->assertDatabaseHas('users', ['email' => 'disabled+admin-at-pos-test@invalid.local']);
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
