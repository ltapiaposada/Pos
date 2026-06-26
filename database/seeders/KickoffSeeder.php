<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KickoffSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $company = Company::query()->first();

        $mainBranch = Branch::query()->updateOrCreate(
            ['code' => 'PRN'],
            [
                'company_id' => $company?->id,
                'name' => 'Sucursal Principal',
                'address' => 'Direccion principal',
                'phone' => '000-0000',
            ]
        );

        Category::query()->updateOrCreate(
            ['name' => 'Bebidas'],
            ['description' => 'Bebidas y refrescos']
        );
        Category::query()->updateOrCreate(
            ['name' => 'Snacks'],
            ['description' => 'Snacks y botanas']
        );

        Customer::query()->updateOrCreate(
            ['document' => 'CF'],
            [
                'company_id' => $company?->id,
                'name' => 'Cliente Mostrador',
                'email' => null,
                'phone' => null,
                'address' => null,
                'contact_type' => Customer::TYPE_PERSON,
                'is_active' => true,
            ]
        );

        if ($this->shouldSeedDemoData()) {
            $this->createUser($company?->id, $mainBranch->id, 'admin@pos.test', 'Administrador', 'password', 'admin');
            $this->createUser($company?->id, $mainBranch->id, 'supervisor@pos.test', 'Supervisor', 'password', 'supervisor');
            $this->createUser($company?->id, $mainBranch->id, 'cashier@pos.test', 'Cajero', 'password', 'cashier');
        } else {
            $this->createProductionAdmin($company?->id, $mainBranch->id);
        }

        // The global owner must be created explicitly per environment.
    }
    private function createProductionAdmin(?int $companyId, ?int $branchId): void
    {
        $email = trim((string) env('POS_INITIAL_ADMIN_EMAIL', ''));
        $password = (string) env('POS_INITIAL_ADMIN_PASSWORD', '');
        $name = trim((string) env('POS_INITIAL_ADMIN_NAME', 'Administrador'));

        if ($email === '' || $password === '' || $companyId === null || $branchId === null) {
            return;
        }

        $this->createUser($companyId, $branchId, $email, $name !== '' ? $name : 'Administrador', $password, 'admin');
    }

    private function createUser(?int $companyId, ?int $branchId, string $email, string $name, string $password, string $role): void
    {
        if ($companyId === null || $branchId === null) {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'password' => Hash::make($password),
            ]
        );

        $user->syncRoles([$role]);
    }
}
