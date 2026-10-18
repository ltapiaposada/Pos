<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Services\CompanyKickoffService;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KickoffSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $kickoff = app(CompanyKickoffService::class);

        foreach (Company::query()->get() as $company) {
            $kickoff->runIfPending($company);
        }

        $company = Company::query()->first();
        $mainBranchId = $company?->branches()->orderBy('id')->value('id');

        if ($this->shouldSeedDemoData()) {
            $this->createUser($company?->id, $mainBranchId, 'admin@pos.test', 'Administrador', 'password', 'admin');
            $this->createUser($company?->id, $mainBranchId, 'supervisor@pos.test', 'Supervisor', 'password', 'supervisor');
            $this->createUser($company?->id, $mainBranchId, 'cashier@pos.test', 'Cajero', 'password', 'cashier');
        } else {
            $this->createProductionAdmin($company?->id, $mainBranchId);
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
