<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        if (env('APP_ENV') === 'local') {
            $middleware->web(append: [
                \App\Http\Middleware\RequestProfiler::class,
            ]);
        }

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'admin.user' => \App\Http\Middleware\EnsureAdminUser::class,
            'customer.user' => \App\Http\Middleware\EnsureCustomerUser::class,
            'system.admin' => \App\Http\Middleware\EnsureSystemAdmin::class,
            'active.subscription' => \App\Http\Middleware\EnsureActiveSubscription::class,
            'pos.company' => \App\Http\Middleware\EnsurePosCompany::class,
            'restaurant.company' => \App\Http\Middleware\EnsureRestaurantCompany::class,
            'storefront.company' => \App\Http\Middleware\ResolveStorefrontCompany::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function ($response, \Throwable $exception, Request $request) {
            if ($response->getStatusCode() === 419 && ! $request->expectsJson()) {
                $target = Route::has('login') ? route('login') : url('/');

                return redirect()->guest($target)
                    ->with('status', 'Tu sesion expiro. Inicia sesion nuevamente.');
            }

            return $response;
        });
    })->create();
