<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyManagementRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\CompanyType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CompanyManagementController extends Controller
{
    public function index(): View
    {
        $companies = Company::query()
            ->with(['companyType', 'latestSubscription', 'effectiveSubscription'])
            ->withCount(['users', 'branches'])
            ->orderBy('name')
            ->paginate(15);

        return view('system.companies.index', compact('companies'));
    }

    public function create(): View
    {
        $companyTypes = CompanyType::query()->where('is_active', true)->orderBy('name')->get();

        return view('system.companies.create', compact('companyTypes'));
    }

    public function store(CompanyManagementRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $company = Company::query()->create([
                'name' => $data['name'],
                'domain' => $data['domain'] ?? null,
                'identification' => $data['identification'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'company_type_id' => $data['company_type_id'],
                'status' => $data['status'],
            ]);

            $mainBranch = Branch::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'name' => 'Sucursal Principal',
                'code' => 'PRN-'.$company->id,
                'address' => $company->address,
                'phone' => $company->phone,
            ]);

            $admin = User::query()->create([
                'company_id' => $company->id,
                'branch_id' => $mainBranch->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
            ]);
            $admin->syncRoles(['admin']);

            if (! empty($data['subscription_billing_period']) && ! empty($data['subscription_start_date']) && ! empty($data['subscription_end_date'])) {
                CompanySubscription::withoutGlobalScopes()->create([
                    'company_id' => $company->id,
                    'plan_type' => $data['subscription_plan_type'] ?? 'pos',
                    'billing_period' => $data['subscription_billing_period'],
                    'start_date' => $data['subscription_start_date'],
                    'end_date' => $data['subscription_end_date'],
                    'status' => $data['subscription_status'] ?? CompanySubscription::STATUS_ACTIVE,
                    'payment_status' => $data['subscription_payment_status'] ?? 'paid',
                    'last_payment_date' => $data['subscription_start_date'],
                    'next_payment_date' => $data['subscription_end_date'],
                ]);
            }
        });

        return redirect()->route('system.companies.index')->with('status', 'Empresa creada correctamente.');
    }

    public function edit(Company $company): View
    {
        $company->load([
            'companyType',
            'latestSubscription',
            'effectiveSubscription',
            'subscriptions' => fn ($query) => $query->orderByDesc('end_date')->limit(10),
        ]);
        $companyTypes = CompanyType::query()->where('is_active', true)->orderBy('name')->get();

        return view('system.companies.edit', compact('company', 'companyTypes'));
    }

    public function update(CompanyManagementRequest $request, Company $company)
    {
        $data = $request->validated();

        $company->update([
            'name' => $data['name'],
            'domain' => $data['domain'] ?? null,
            'identification' => $data['identification'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'company_type_id' => $data['company_type_id'],
            'status' => $data['status'],
        ]);

        return redirect()->route('system.companies.edit', $company)->with('status', 'Empresa actualizada.');
    }
}
