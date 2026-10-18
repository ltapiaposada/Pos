<?php

use App\Http\Controllers\StorefrontCartController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\StorefrontCustomerAuthController;
use App\Http\Controllers\StorefrontCustomerPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/preview/{companyId}', [StorefrontController::class, 'preview'])->name('storefront.preview');
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/ingresar', [StorefrontCustomerAuthController::class, 'login'])->name('shop.login');
Route::post('/ingresar', [StorefrontCustomerAuthController::class, 'authenticate'])->name('shop.login.store');
Route::get('/registrate', [StorefrontCustomerAuthController::class, 'create'])->name('shop.register');
Route::post('/registrate', [StorefrontCustomerAuthController::class, 'store'])->name('shop.register.store');
Route::post('/salir', [StorefrontCustomerAuthController::class, 'destroy'])->name('shop.logout');
Route::get('/recuperar-contrasena', [StorefrontCustomerPasswordController::class, 'requestForm'])->name('shop.password.request');
Route::post('/recuperar-contrasena', [StorefrontCustomerPasswordController::class, 'sendLink'])->name('shop.password.email');
Route::get('/restablecer-contrasena/{token}', [StorefrontCustomerPasswordController::class, 'resetForm'])->name('password.reset');
Route::post('/restablecer-contrasena', [StorefrontCustomerPasswordController::class, 'reset'])->name('shop.password.update');
Route::get('/carrito', [StorefrontCartController::class, 'cart'])->name('shop.cart');
Route::post('/carrito', [StorefrontCartController::class, 'add'])->name('shop.cart.add');
Route::patch('/carrito/{lineKey}', [StorefrontCartController::class, 'update'])->name('shop.cart.update');
Route::delete('/carrito/{lineKey}', [StorefrontCartController::class, 'remove'])->name('shop.cart.remove');
Route::get('/checkout', [StorefrontCartController::class, 'checkout'])->name('shop.checkout');
Route::post('/checkout', [StorefrontCartController::class, 'placeOrder'])->name('shop.place-order');
Route::get('/mis-pedidos', [StorefrontCartController::class, 'orders'])->name('shop.orders.index');
Route::get('/mis-pedidos/{externalOrderUuid}', [StorefrontCartController::class, 'orderShow'])->name('shop.orders.show');
