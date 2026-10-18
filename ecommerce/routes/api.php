<?php

use App\Http\Controllers\Internal\PosEventController;
use App\Http\Controllers\Internal\TenantController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::post('checkout', [CheckoutController::class, 'store']);

Route::prefix('internal/v1')->middleware('internal.service')->group(function (): void {
    Route::post('tenants/provision', [TenantController::class, 'provision']);
    Route::patch('tenants/{tenantUuid}/storefront', [TenantController::class, 'updateStorefront']);
    Route::post('events/pos', [PosEventController::class, 'store']);
});
