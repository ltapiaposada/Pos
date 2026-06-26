<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashRegisterSession;
use App\Models\Company;
use App\Models\CompanyType;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BusinessCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_sale_uses_business_currency(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@pos.test')->firstOrFail();
        Setting::query()->updateOrCreate(
            ['company_id' => $admin->company_id, 'key' => 'business'],
            ['value' => ['name' => 'Empresa Demo', 'currency' => 'COP', 'allow_negative_stock' => false]]
        );
        Setting::forgetValue('business', $admin->company_id);

        CashRegisterSession::query()->create([
            'branch_id' => $admin->branch_id,
            'user_id' => $admin->id,
            'opened_at' => now(),
            'opening_amount' => 50,
            'status' => 'open',
        ]);

        $customerId = Customer::query()->where('document', 'CF')->value('id');

        $this->actingAs($admin)
            ->post(route('pos.checkout'), [
                'branch_id' => $admin->branch_id,
                'customer_id' => $customerId,
                'items' => json_encode([
                    [
                        'product_id' => 1,
                        'quantity' => 2,
                        'unit_price' => 1.20,
                    ],
                ]),
                'payments' => json_encode([
                    [
                        'method' => 'cash',
                        'amount' => 2.78,
                    ],
                ]),
            ])
            ->assertRedirect();

        $sale = Sale::query()->latest('id')->firstOrFail();

        $this->assertSame('COP', $sale->currency);
    }

    public function test_ecommerce_order_uses_business_currency(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'customer']);

        $companyType = CompanyType::query()->firstOrCreate(
            ['slug' => 'pos'],
            [
                'name' => 'POS',
                'features' => ['sales', 'products', 'inventory'],
                'is_active' => true,
            ]
        );

        $company = Company::query()->create([
            'name' => 'Tienda Moneda',
            'domain' => 'localhost',
            'company_type_id' => $companyType->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        $branch = Branch::query()->create([
            'company_id' => $company->id,
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        Setting::query()->updateOrCreate(
            ['company_id' => $company->id, 'key' => 'business'],
            ['value' => ['name' => 'Tienda Moneda', 'currency' => 'EUR', 'allow_negative_stock' => false]]
        );
        Setting::forgetValue('business', $company->id);

        $tax = Tax::query()->create([
            'company_id' => $company->id,
            'name' => 'IVA',
            'rate' => 19,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Producto moneda web',
            'sku' => 'WEB-CUR-001',
            'tax_id' => $tax->id,
            'unit' => 'und',
            'sale_price' => 100,
            'cost_price' => 50,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        Inventory::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'stock' => 10,
            'min_stock' => 1,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Cliente Moneda',
            'email' => 'currency-customer@test.com',
            'password' => 'password',
        ]);
        $user->assignRole('customer');

        Customer::query()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['shop.cart' => [$product->id => 1]])
            ->post('/checkout', [
                'address' => 'Calle 123',
                'phone' => '5551234',
                'payment_method' => 'transfer',
                'payment_reference' => 'TRX-CURRENCY-001',
            ])
            ->assertRedirect();

        $sale = Sale::query()
            ->where('order_source', Sale::SOURCE_ECOMMERCE)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('EUR', $sale->currency);
    }

    public function test_ecommerce_order_uses_business_shipping_and_coupon_settings(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'customer']);

        $companyType = CompanyType::query()->firstOrCreate(
            ['slug' => 'pos'],
            [
                'name' => 'POS',
                'features' => ['sales', 'products', 'inventory'],
                'is_active' => true,
            ]
        );

        $company = Company::query()->create([
            'name' => 'Tienda Checkout',
            'domain' => 'localhost',
            'company_type_id' => $companyType->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        $branch = Branch::query()->create([
            'company_id' => $company->id,
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        Setting::query()->updateOrCreate(
            ['company_id' => $company->id, 'key' => 'business'],
            ['value' => [
                'name' => 'Tienda Checkout',
                'currency' => 'USD',
                'ecommerce_flat_shipping' => 12.5,
                'ecommerce_coupons' => ['LOCAL20' => 20],
                'allow_negative_stock' => false,
            ]]
        );
        Setting::forgetValue('business', $company->id);

        $tax = Tax::query()->create([
            'company_id' => $company->id,
            'name' => 'IVA',
            'rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Producto checkout web',
            'sku' => 'WEB-CFG-001',
            'tax_id' => $tax->id,
            'unit' => 'und',
            'sale_price' => 100,
            'cost_price' => 50,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        Inventory::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'stock' => 10,
            'min_stock' => 1,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Cliente Checkout',
            'email' => 'checkout-customer@test.com',
            'password' => 'password',
        ]);
        $user->assignRole('customer');

        Customer::query()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['shop.cart' => [$product->id => 1]])
            ->post('/checkout', [
                'address' => 'Calle 123',
                'phone' => '5551234',
                'payment_method' => 'transfer',
                'payment_reference' => 'TRX-CFG-001',
                'coupon_code' => 'local20',
            ])
            ->assertRedirect();

        $sale = Sale::query()
            ->where('order_source', Sale::SOURCE_ECOMMERCE)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(12.5, (float) $sale->shipping_total);
        $this->assertSame(20.0, (float) $sale->coupon_discount_total);
        $this->assertSame('LOCAL20', $sale->coupon_code);
        $this->assertSame(92.5, (float) $sale->total);
    }

    public function test_database_seeder_skips_demo_data_when_disabled(): void
    {
        $this->setDemoSeedFlag(false);

        try {
            $this->seed(DatabaseSeeder::class);

            $this->assertDatabaseMissing('users', ['email' => 'admin@pos.test']);
            $this->assertDatabaseMissing('users', ['email' => 'supervisor@pos.test']);
            $this->assertDatabaseMissing('users', ['email' => 'cashier@pos.test']);
            $this->assertDatabaseCount('products', 0);
            $this->assertDatabaseHas('customers', ['document' => 'CF']);
            $this->assertDatabaseMissing('customers', ['document' => 'NIT-123456']);
        } finally {
            $this->setDemoSeedFlag(true);
        }
    }

    private function setDemoSeedFlag(bool $enabled): void
    {
        $value = $enabled ? 'true' : 'false';

        putenv("SEED_DEMO_DATA={$value}");
        $_ENV['SEED_DEMO_DATA'] = $value;
        $_SERVER['SEED_DEMO_DATA'] = $value;
    }
}
