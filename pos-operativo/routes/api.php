<?php

use App\Http\Controllers\Api\InternalCustomerAccessController;
use App\Http\Controllers\Api\InternalOrderController;
use App\Http\Controllers\Api\InternalTenantController;
use App\Http\Controllers\Api\InternalUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('internal.service')->group(function (): void {
    Route::get('tenants/{tenantUuid}/catalog', [InternalTenantController::class, 'catalog']);
    Route::get('tenants/{tenantUuid}/availability', [InternalTenantController::class, 'availability']);
    Route::post('tenants/provision', [InternalTenantController::class, 'provision']);
    Route::post('tenants/{tenantUuid}/contacts', [InternalCustomerAccessController::class, 'register']);
    Route::post('tenants/{tenantUuid}/contacts/login', [InternalCustomerAccessController::class, 'login']);
    Route::post('tenants/{tenantUuid}/contacts/lookup', [InternalCustomerAccessController::class, 'findByEmail']);
    Route::patch('tenants/{tenantUuid}/contacts/{externalCustomerUuid}', [InternalCustomerAccessController::class, 'update']);
    Route::get('tenants/{tenantUuid}/users', [InternalUserController::class, 'index']);
    Route::post('tenants/{tenantUuid}/users', [InternalUserController::class, 'store']);
    Route::get('tenants/{tenantUuid}/users/{userId}', [InternalUserController::class, 'show']);
    Route::patch('tenants/{tenantUuid}/users/{userId}', [InternalUserController::class, 'update']);
    Route::post('tenants/{tenantUuid}/users/{userId}/password-reset', [InternalUserController::class, 'sendPasswordReset']);
    Route::post('orders', [InternalOrderController::class, 'store']);
    Route::patch('orders/{externalOrderUuid}/status', [InternalOrderController::class, 'updateStatus']);
});
