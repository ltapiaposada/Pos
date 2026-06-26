<?php

namespace App\Http\Middleware;

use App\Support\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePosCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! CompanyContext::supportsClassicPos($user->company)) {
            abort(403, 'Este modulo solo esta disponible cuando el servicio activo es POS u Optica.');
        }

        return $next($request);
    }
}
