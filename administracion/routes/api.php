<?php

use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('internal.service')->group(function (): void {
    Route::post('tenants', [TenantController::class, 'store']);
    Route::post('tenants/{tenantUuid}/provisioning/{jobId}/retry', [TenantController::class, 'retryProvisioning']);
    Route::patch('tenants/{tenantUuid}/entitlements', [TenantController::class, 'updateEntitlements']);
});
