<?php

namespace App\Http\Middleware;

use App\Support\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOpticCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! CompanyContext::isOpticService($user->company)) {
            abort(403, 'El modulo de optometria solo esta disponible cuando el servicio activo es Optica.');
        }

        return $next($request);
    }
}
