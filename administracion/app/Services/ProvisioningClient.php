<?php

namespace App\Services;

use App\Models\ProvisioningJob;
use Illuminate\Support\Facades\Http;

class ProvisioningClient
{
    public function execute(ProvisioningJob $job): void
    {
        $baseUrl = match ($job->target) {
            'pos' => config('integration.pos_url'),
            'ecommerce' => config('integration.ecommerce_url'),
        };

        $path = match ([$job->target, $job->action]) {
            ['pos', 'provision'] => '/api/v1/tenants/provision',
            ['ecommerce', 'provision'] => '/api/internal/v1/tenants/provision',
            default => throw new \LogicException("Accion de aprovisionamiento no soportada: {$job->target}/{$job->action}"),
        };

        Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withHeader('X-Internal-Token', config('integration.internal_token'))
            ->timeout(10)
            ->post($path, $job->payload)
            ->throw();
    }
}
