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

        if (CompanyContext::activeSubscriptionPlanType($user->company) !== 'restaurant') {
            abort(403, 'El modulo restaurante solo esta disponible para empresas tipo restaurante.');
        }

        return $next($request);
    }
}
