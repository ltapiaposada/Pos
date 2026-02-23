<?php

use App\Http\Controllers\Accounting\AccountController as AccountingAccountController;
use App\Http\Controllers\Accounting\CreditControlController;
use App\Http\Controllers\Accounting\ExpenseController;
use App\Http\Controllers\Accounting\JournalEntryController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EcommerceOrderManagementController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('shop.index');
Route::get('/tienda', [StorefrontController::class, 'index'])->name('shop.catalog');
Route::get('/carrito', [StorefrontController::class, 'cart'])->name('shop.cart');
Route::post('/carrito/items', [StorefrontController::class, 'addToCart'])->name('shop.cart.add');
Route::patch('/carrito/items/{product}', [StorefrontController::class, 'updateCartItem'])->name('shop.cart.update');
Route::delete('/carrito/items/{product}', [StorefrontController::class, 'removeCartItem'])->name('shop.cart.remove');

Route::middleware(['auth', 'customer.user'])->group(function () {
    Route::get('/checkout', [StorefrontController::class, 'checkout'])->name('shop.checkout');
    Route::post('/checkout', [StorefrontController::class, 'placeOrder'])->name('shop.place-order');
    Route::get('/mis-pedidos', [StorefrontController::class, 'orders'])->name('shop.orders.index');
    Route::get('/mis-pedidos/{sale}', [StorefrontController::class, 'orderShow'])->name('shop.orders.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin.user'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    Route::resource('categories', CategoryController::class)->middleware('permission:manage_categories');
    Route::resource('branches', BranchController::class)
        ->except(['show', 'destroy'])
        ->middleware('permission:manage_branches');
    Route::resource('products', ProductController::class)->middleware('permission:manage_products');
    Route::resource('customers', CustomerController::class)->middleware('permission:manage_customers');

    Route::prefix('security')->name('security.')->middleware('permission:manage_users')->group(function () {
        Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');

        Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::put('roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
    });

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index')->middleware('permission:manage_inventory');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust')->middleware('permission:manage_inventory');

    Route::get('pos', [PosController::class, 'index'])->name('pos.index')->middleware('permission:create_sale');
    Route::get('pos/products', [PosController::class, 'products'])->name('pos.products')->middleware('permission:create_sale');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout')->middleware('permission:create_sale');
    Route::get('sales', [PosController::class, 'invoices'])->name('sales.index')->middleware('permission:create_sale');
    Route::get('sales/{sale}', [PosController::class, 'show'])->name('sales.show')->middleware('permission:create_sale');
    Route::get('sales/{sale}/ticket', [PosController::class, 'ticket'])->name('sales.ticket')->middleware('permission:create_sale');

    Route::get('cash-register', [CashRegisterController::class, 'index'])->name('cash-register.index')->middleware('permission:open_cash_register');
    Route::post('cash-register/open', [CashRegisterController::class, 'open'])->name('cash-register.open')->middleware('permission:open_cash_register');
    Route::post('cash-register/close', [CashRegisterController::class, 'close'])->name('cash-register.close')->middleware('permission:close_cash_register');
    Route::post('cash-register/movement', [CashRegisterController::class, 'movement'])->name('cash-register.movement')->middleware('permission:record_cash_movement');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:view_reports');
    Route::prefix('ecommerce')->name('ecommerce-admin.')->middleware('permission:manage_ecommerce_orders')->group(function () {
        Route::get('orders', [EcommerceOrderManagementController::class, 'index'])->name('orders.index');
        Route::get('orders/{sale}', [EcommerceOrderManagementController::class, 'show'])->name('orders.show');
        Route::patch('orders/{sale}/status', [EcommerceOrderManagementController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{sale}/invoice', [EcommerceOrderManagementController::class, 'convertToInvoice'])->name('orders.invoice');
    });

    Route::get('returns/create', [ReturnController::class, 'create'])->name('returns.create')->middleware('permission:process_return');
    Route::post('returns', [ReturnController::class, 'store'])->name('returns.store')->middleware('permission:process_return');
    Route::get('returns/{return}', [ReturnController::class, 'show'])->name('returns.show')->middleware('permission:process_return');

    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index')->middleware('permission:manage_purchases');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create')->middleware('permission:manage_purchases');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store')->middleware('permission:manage_purchases');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show')->middleware('permission:manage_purchases');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit')->middleware('permission:manage_settings');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update')->middleware('permission:manage_settings');
    Route::post('settings/logo-upload', [SettingController::class, 'uploadLogo'])->name('settings.logo-upload')->middleware('permission:manage_settings');
    Route::post('settings/qr-upload', [SettingController::class, 'uploadQr'])->name('settings.qr-upload')->middleware('permission:manage_settings');

    Route::prefix('accounting')->name('accounting.')->middleware('permission:manage_accounting')->group(function () {
        Route::get('accounts', [AccountingAccountController::class, 'index'])->name('accounts.index');
        Route::get('accounts/create', [AccountingAccountController::class, 'create'])->name('accounts.create');
        Route::post('accounts', [AccountingAccountController::class, 'store'])->name('accounts.store');
        Route::get('accounts/{account}/edit', [AccountingAccountController::class, 'edit'])->name('accounts.edit');
        Route::put('accounts/{account}', [AccountingAccountController::class, 'update'])->name('accounts.update');

        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('receivables', [CreditControlController::class, 'receivablesIndex'])->name('receivables.index');
        Route::post('receivables/{sale}/collect', [CreditControlController::class, 'collectReceivable'])->name('receivables.collect');
        Route::post('receivables/{sale}/payments/{payment}/void', [CreditControlController::class, 'voidReceivablePayment'])->name('receivables.payments.void');
        Route::get('payables', [CreditControlController::class, 'payablesIndex'])->name('payables.index');
        Route::post('payables/{purchase}/pay', [CreditControlController::class, 'payPayable'])->name('payables.pay');
        Route::post('payables/{purchase}/payments/{purchasePayment}/void', [CreditControlController::class, 'voidPayablePayment'])->name('payables.payments.void');

        Route::get('entries', [JournalEntryController::class, 'index'])->name('entries.index');
        Route::get('entries/create', [JournalEntryController::class, 'create'])->name('entries.create');
        Route::post('entries', [JournalEntryController::class, 'store'])->name('entries.store');
        Route::get('entries/movements', [JournalEntryController::class, 'movements'])->name('entries.movements');
        Route::get('entries/{entry}', [JournalEntryController::class, 'show'])->name('entries.show');
        Route::get('opening-balances', [JournalEntryController::class, 'openingBalancesForm'])->name('opening-balances.form');
        Route::post('opening-balances', [JournalEntryController::class, 'storeOpeningBalances'])->name('opening-balances.store');
        Route::get('income-statement', [JournalEntryController::class, 'incomeStatement'])->name('income-statement');
        Route::get('income-statement/export', [JournalEntryController::class, 'exportIncomeStatement'])->name('income-statement.export');
        Route::get('close-period', [JournalEntryController::class, 'closePeriodForm'])->name('close-period.form');
        Route::put('close-period', [JournalEntryController::class, 'closePeriod'])->name('close-period.store');
    });
});

require __DIR__.'/auth.php';
