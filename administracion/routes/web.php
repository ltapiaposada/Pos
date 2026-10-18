<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserDirectoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/companies', [DashboardController::class, 'companies'])->name('companies.index');
Route::get('/companies/create', [DashboardController::class, 'create'])->name('companies.create');
Route::post('/companies', [DashboardController::class, 'store'])->name('companies.store');
Route::get('/companies/{company}/activation', [DashboardController::class, 'activation'])->name('companies.activation');
Route::get('/companies/{company}', [DashboardController::class, 'show'])->name('companies.show');
Route::get('/companies/{company}/edit', [DashboardController::class, 'edit'])->name('companies.edit');
Route::get('/companies/{company}/applications', [DashboardController::class, 'applications'])->name('companies.applications');
Route::get('/companies/{company}/subscriptions', [DashboardController::class, 'subscriptions'])->name('companies.subscriptions');
Route::get('/companies/{company}/provisioning', [DashboardController::class, 'provisioning'])->name('companies.provisioning');
Route::put('/companies/{company}', [DashboardController::class, 'update'])->name('companies.update');
Route::put('/companies/{company}/entitlements', [DashboardController::class, 'updateEntitlements'])->name('companies.entitlements.update');
Route::post('/companies/{company}/subscriptions', [DashboardController::class, 'storeSubscription'])->name('companies.subscriptions.store');
Route::put('/companies/{company}/subscriptions/{subscription}', [DashboardController::class, 'updateSubscription'])->name('companies.subscriptions.update');
Route::delete('/companies/{company}/subscriptions/{subscription}', [DashboardController::class, 'destroySubscription'])->name('companies.subscriptions.destroy');
Route::post('/companies/{company}/provision/{service}', [DashboardController::class, 'provision'])->name('companies.provision');
Route::post('/companies/{company}/provisioning/{job}/retry', [DashboardController::class, 'retryProvisioning'])->name('companies.provisioning.retry');
Route::post('/services', [DashboardController::class, 'storeService'])->name('services.store');
Route::get('/services/{service}/edit', [DashboardController::class, 'editService'])->name('services.edit');
Route::put('/services/{service}', [DashboardController::class, 'updateService'])->name('services.update');
Route::delete('/services/{service}', [DashboardController::class, 'destroyService'])->name('services.destroy');
Route::post('/plans', [DashboardController::class, 'storePlan'])->name('plans.store');
Route::get('/plans/{plan}/edit', [DashboardController::class, 'editPlan'])->name('plans.edit');
Route::put('/plans/{plan}', [DashboardController::class, 'updatePlan'])->name('plans.update');
Route::delete('/plans/{plan}', [DashboardController::class, 'destroyPlan'])->name('plans.destroy');
Route::get('/catalog', [DashboardController::class, 'catalog'])->name('catalog.index');
Route::get('/users', [UserDirectoryController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserDirectoryController::class, 'create'])->name('users.create');
Route::post('/users', [UserDirectoryController::class, 'store'])->name('users.store');
Route::get('/users/{company}/{userId}/edit', [UserDirectoryController::class, 'edit'])->name('users.edit');
Route::put('/users/{company}/{userId}', [UserDirectoryController::class, 'update'])->name('users.update');
Route::post('/users/{company}/{userId}/password-reset', [UserDirectoryController::class, 'sendReset'])->name('users.password-reset');
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::get('/companies/{company}/payments', [PaymentController::class, 'company'])->name('companies.payments');
Route::post('/companies/{company}/payments', [PaymentController::class, 'store'])->name('companies.payments.store');
Route::put('/companies/{company}/payments/{payment}', [PaymentController::class, 'update'])->name('companies.payments.update');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
