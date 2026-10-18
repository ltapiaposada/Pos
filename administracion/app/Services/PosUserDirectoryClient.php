<?php
namespace App\Services;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
class PosUserDirectoryClient
{
    private function request() { return Http::baseUrl(config('integration.pos_url'))->acceptJson()->asJson()->withHeader('X-Internal-Token', config('integration.internal_token'))->timeout(10); }
    public function list(Tenant $company): array { return $this->request()->get("/api/v1/tenants/{$company->tenant_uuid}/users")->throw()->json('users') ?? []; }
    public function find(Tenant $company, int $userId): array { return $this->request()->get("/api/v1/tenants/{$company->tenant_uuid}/users/{$userId}")->throw()->json('user'); }
    public function create(Tenant $company, array $data): array { return $this->request()->post("/api/v1/tenants/{$company->tenant_uuid}/users", $data)->throw()->json('user'); }
    public function update(Tenant $company, int $userId, array $data): array { return $this->request()->patch("/api/v1/tenants/{$company->tenant_uuid}/users/{$userId}", $data)->throw()->json('user'); }
    public function sendReset(Tenant $company, int $userId): void { $this->request()->post("/api/v1/tenants/{$company->tenant_uuid}/users/{$userId}/password-reset")->throw(); }
}
