<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Services\SubscriptionAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InternalTenantController extends Controller
{
    public function catalog(Request $request, string $tenantUuid): JsonResponse
    {
        $company = $this->resolveCompany($request, $tenantUuid);
        abort_unless(app(SubscriptionAccessService::class)->canUseService($company), 402, 'El servicio POS no está habilitado o su suscripción no está pagada.');

        $products = Product::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->where('is_visible_ecommerce', true)
            ->where('product_type', '!=', Product::TYPE_VARIANT)
            ->with(['category:id,name', 'tax:id,rate', 'variants' => fn ($query) => $query
                ->where('is_active', true)
                ->where('is_visible_ecommerce', true)
                ->select(['id', 'parent_product_id', 'name', 'sku', 'sale_price'])])
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'product_type' => $product->product_type,
                'description' => $product->description,
                'image_url' => $product->image_url,
                'price' => $product->sale_price,
                'currency' => config('pos.default_currency', 'USD'),
                'tax_rate' => $product->tax?->rate ?? 0,
                'category' => $product->category?->name,
                'variants' => $product->variants->map(fn (Product $variant) => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'price' => $variant->sale_price,
                ])->values(),
                'updated_at' => $product->updated_at?->toAtomString(),
            ])->values();

        return response()->json(['tenant_uuid' => $company->tenant_uuid, 'products' => $products]);
    }

    public function availability(Request $request, string $tenantUuid): JsonResponse
    {
        $company = $this->resolveCompany($request, $tenantUuid);
        abort_unless(app(SubscriptionAccessService::class)->canUseService($company), 402, 'El servicio POS no está habilitado o su suscripción no está pagada.');
        $branch = Branch::withoutGlobalScopes()->where('company_id', $company->id)->orderBy('id')->firstOrFail();

        $availability = Inventory::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->get(['product_id', 'stock'])
            ->map(fn (Inventory $inventory) => [
                'product_id' => $inventory->product_id,
                'stock' => (float) $inventory->stock,
            ])->values();

        return response()->json(['tenant_uuid' => $company->tenant_uuid, 'branch_id' => $branch->id, 'availability' => $availability]);
    }

    public function provision(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'admin' => ['nullable', 'array'],
            'admin.name' => ['nullable', 'string', 'max:255'],
            'admin.email' => ['nullable', 'email', 'max:255'],
        ]);

        // Administración es la única dueña de la compañía. POS solo crea sus
        // recursos operativos usando el mismo company_id lógico. El fallback
        // local existe únicamente para las pruebas aisladas del POS.
        $company = app()->environment('testing')
            ? Company::withoutGlobalScopes()->firstOrCreate(
                ['tenant_uuid' => $data['tenant_uuid']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'status' => Company::STATUS_ACTIVE,
                ]
            )
            : Company::withoutGlobalScopes()->where('tenant_uuid', $data['tenant_uuid'])->firstOrFail();

        abort_unless(app(SubscriptionAccessService::class)->canUseService($company), 422, 'El servicio POS debe estar habilitado y tener una suscripción pagada antes de aprovisionar.');

        DB::transaction(function () use ($data, $company): void {

            $branch = Branch::withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'PRN-'.strtoupper(Str::substr($company->tenant_uuid, 0, 8))],
                ['name' => 'Sucursal Principal', 'address' => $company->address, 'phone' => $company->phone]
            );

            $admin = $data['admin'] ?? [];
            if (! empty($admin['email'])) {
                User::withoutGlobalScopes()->firstOrCreate(
                    ['email' => $admin['email']],
                    [
                        'company_id' => $company->id,
                        'branch_id' => $branch->id,
                        'name' => $admin['name'] ?? 'Administrador',
                        'password' => Hash::make(Str::password(32)),
                    ]
                );
            }

        });

        return response()->json(['tenant_uuid' => $company->tenant_uuid, 'company_id' => $company->id], 201);
    }

    private function resolveCompany(Request $request, string $tenantUuid): Company
    {
        $company = Company::withoutGlobalScopes()->where('tenant_uuid', $tenantUuid)->firstOrFail();
        $request->attributes->set('public_company_id', $company->id);
        $request->attributes->set('public_company', $company);

        return $company;
    }
}
