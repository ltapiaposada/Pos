<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternalService
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('integration.internal_token');
        $provided = (string) $request->header('X-Internal-Token');

        abort_unless($expected !== '' && hash_equals($expected, $provided), 401, 'Servicio interno no autorizado.');

        return $next($request);
    }
}
