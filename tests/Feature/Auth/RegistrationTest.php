<?php

namespace Tests\Feature\Auth;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyType;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Role::firstOrCreate(['name' => 'customer']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('shop.index', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'email' => 'test@example.com',
        ]);
    }

    public function test_new_customer_registration_uses_public_store_company_from_domain(): void
    {
        Role::firstOrCreate(['name' => 'customer']);

        $type = CompanyType::query()->firstOrCreate(
            ['slug' => 'restaurant'],
            [
                'name' => 'Restaurante',
                'features' => ['ecommerce', 'restaurant'],
                'is_active' => true,
            ]
        );

        $company = Company::query()->create([
            'name' => 'Mi Restaurante',
            'domain' => 'mirestaurante.test',
            'company_type_id' => $type->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        $branch = Branch::query()->create([
            'company_id' => $company->id,
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        $response = $this->post('http://mirestaurante.test/register', [
            'name' => 'Cliente Tienda',
            'email' => 'cliente@tienda.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('shop.index', absolute: false));

        $user = User::query()->where('email', 'cliente@tienda.test')->first();
        $this->assertNotNull($user);
        $this->assertSame($company->id, $user->company_id);
        $this->assertSame($branch->id, $user->branch_id);
        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'email' => 'cliente@tienda.test',
        ]);
    }
}
