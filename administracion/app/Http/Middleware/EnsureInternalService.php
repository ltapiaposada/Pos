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
        abort_unless($expected !== '' && hash_equals($expected, (string) $request->header('X-Internal-Token')), 401);

        return $next($request);
    }
}
