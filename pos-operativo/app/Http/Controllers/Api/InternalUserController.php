<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\PhpEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InternalUserController extends Controller
{
    public function index(string $tenantUuid): JsonResponse
    {
        $company = $this->company($tenantUuid);

        return response()->json(['users' => User::withoutGlobalScopes()->where('company_id', $company->id)->orderBy('name')->get(['id', 'company_id', 'branch_id', 'name', 'email', 'is_active', 'created_at'])]);
    }

    public function show(string $tenantUuid, int $userId): JsonResponse
    {
        return response()->json(['user' => $this->user($tenantUuid, $userId)->only(['id', 'company_id', 'branch_id', 'name', 'email', 'is_active', 'created_at'])]);
    }

    public function store(Request $request, string $tenantUuid): JsonResponse
    {
        $company = $this->company($tenantUuid);
        $data = $this->data($request, $company);
        $user = User::withoutGlobalScopes()->create([...$data, 'company_id' => $company->id, 'branch_id' => $data['branch_id'] ?? Branch::withoutGlobalScopes()->where('company_id', $company->id)->value('id'), 'password' => Hash::make(Str::password(32))]);

        return response()->json(['user' => $user->only(['id', 'name', 'email', 'is_active'])], 201);
    }

    public function update(Request $request, string $tenantUuid, int $userId): JsonResponse
    {
        $company = $this->company($tenantUuid);
        $user = $this->user($tenantUuid, $userId);
        $user->update($this->data($request, $company, $user));

        return response()->json(['user' => $user->fresh()->only(['id', 'name', 'email', 'is_active'])]);
    }

    public function sendPasswordReset(string $tenantUuid, int $userId, PhpEmailService $mail): JsonResponse
    {
        $user = $this->user($tenantUuid, $userId);
        $mail->sendPasswordReset($user);

        return response()->json(['sent' => true]);
    }

    private function company(string $tenantUuid): Company
    {
        return Company::withoutGlobalScopes()->where('tenant_uuid', $tenantUuid)->firstOrFail();
    }

    private function user(string $tenantUuid, int $userId): User
    {
        $company = $this->company($tenantUuid);

        return User::withoutGlobalScopes()->where('company_id', $company->id)->findOrFail($userId);
    }

    private function data(Request $request, Company $company, ?User $user = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)], 'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $company->id)], 'is_active' => ['required', 'boolean']]);
    }
}
