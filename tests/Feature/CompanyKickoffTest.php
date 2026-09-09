<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanyKickoffTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_company_runs_kickoff_on_first_login(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $type = CompanyType::query()->firstOrCreate(['slug' => 'pos'], [
            'name' => 'POS',
            'features' => ['sales', 'products', 'inventory'],
            'is_active' => true,
        ]);
        $company = Company::query()->create([
            'name' => 'Empresa nueva',
            'company_type_id' => $type->id,
            'status' => Company::STATUS_ACTIVE,
            'kickoff_completed' => false,
        ]);
        $user = User::query()->create([
            'company_id' => $company->id,
            'name' => 'Administrador',
            'email' => 'kickoff@example.test',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('admin');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertTrue($company->fresh()->kickoff_completed);
        $this->assertDatabaseHas('branches', ['company_id' => $company->id, 'name' => 'Sucursal Principal']);
        $this->assertDatabaseHas('categories', ['company_id' => $company->id, 'name' => 'Bebidas']);
        $this->assertDatabaseHas('customers', [
            'company_id' => $company->id,
            'name' => 'Consumidor Final',
            'document' => '222222222222',
        ]);
        $this->assertDatabaseHas('taxes', ['company_id' => $company->id, 'name' => 'IVA 16%']);
        $this->assertDatabaseHas('settings', ['company_id' => $company->id, 'key' => 'business']);
        $this->assertDatabaseHas('accounting_accounts', ['company_id' => $company->id, 'code' => '1105']);
    }

    public function test_completed_company_does_not_run_kickoff_again(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $type = CompanyType::query()->firstOrCreate(['slug' => 'pos'], [
            'name' => 'POS',
            'features' => [],
            'is_active' => true,
        ]);
        $company = Company::query()->create([
            'name' => 'Empresa configurada',
            'company_type_id' => $type->id,
            'status' => Company::STATUS_ACTIVE,
            'kickoff_completed' => true,
        ]);
        $user = User::query()->create([
            'company_id' => $company->id,
            'name' => 'Administrador',
            'email' => 'configured@example.test',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('admin');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertDatabaseMissing('customers', [
            'company_id' => $company->id,
            'document' => '222222222222',
        ]);
    }
}
