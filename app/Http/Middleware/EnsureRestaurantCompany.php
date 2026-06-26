<?php

namespace App\Http\Middleware;

use App\Support\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! CompanyContext::isRestaurantService($user->company)) {
            abort(403, 'El modulo restaurante solo esta disponible cuando el servicio activo es Restaurante.');
        }

        return $next($request);
    }
}
