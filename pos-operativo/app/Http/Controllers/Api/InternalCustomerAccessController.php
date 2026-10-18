<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Services\SubscriptionAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** API interna para la cuenta compradora. El contacto del POS es la fuente de verdad. */
class InternalCustomerAccessController extends Controller
{
    public function register(Request $request, string $tenantUuid): JsonResponse
    {
        $data = $this->contactData($request, true);
        $company = $this->company($request, $tenantUuid);

        if (Customer::withoutGlobalScopes()->where('company_id', $company->id)->where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'El contacto ya existe. Debe restablecer su contraseña para ingresar.'], 409);
        }

        $customer = Customer::withoutGlobalScopes()->create([
            ...$data,
            'company_id' => $company->id,
            'external_customer_uuid' => (string) Str::uuid(),
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        return response()->json(['customer' => $this->customerPayload($customer)], 201);
    }

    public function login(Request $request, string $tenantUuid): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $company = $this->company($request, $tenantUuid);
        $customer = Customer::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('email', $data['email'])
            ->first();

        if (! $customer || ! $customer->is_active || ! $customer->password || ! Hash::check($data['password'], $customer->password)) {
            return response()->json(['message' => 'Las credenciales no son válidas para esta tienda.'], 422);
        }

        return response()->json(['customer' => $this->customerPayload($customer)]);
    }

    public function findByEmail(Request $request, string $tenantUuid): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $company = $this->company($request, $tenantUuid);
        $customer = Customer::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('email', $data['email'])
            ->firstOrFail();

        if (! $customer->external_customer_uuid) {
            $customer->forceFill(['external_customer_uuid' => (string) Str::uuid()])->save();
        }

        return response()->json(['customer' => $this->customerPayload($customer)]);
    }

    public function update(Request $request, string $tenantUuid, string $externalCustomerUuid): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        $company = $this->company($request, $tenantUuid);
        $customer = Customer::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('external_customer_uuid', $externalCustomerUuid)
            ->firstOrFail();

        if (array_key_exists('password', $data) && $data['password'] !== null) {
            $data['password'] = Hash::make($data['password']);
        }
        $customer->fill($data)->save();

        return response()->json(['customer' => $this->customerPayload($customer)]);
    }

    private function company(Request $request, string $tenantUuid): Company
    {
        $company = Company::withoutGlobalScopes()->where('tenant_uuid', $tenantUuid)->firstOrFail();
        abort_unless(app(SubscriptionAccessService::class)->canUseService($company), 402, 'El servicio POS no está habilitado o su suscripción no está pagada.');
        $request->attributes->set('public_company_id', $company->id);

        return $company;
    }

    /** @return array<string, mixed> */
    private function contactData(Request $request, bool $withPassword = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
        if ($withPassword) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        return $request->validate($rules);
    }

    /** @return array<string, mixed> */
    private function customerPayload(Customer $customer): array
    {
        return [
            'external_customer_uuid' => $customer->external_customer_uuid,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
        ];
    }
}
