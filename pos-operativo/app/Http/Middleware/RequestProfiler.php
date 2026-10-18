<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestProfiler
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $dbMs = 0.0;
        $dbQueries = 0;

        DB::listen(function ($query) use (&$dbMs, &$dbQueries): void {
            $dbQueries++;
            $dbMs += (float) ($query->time ?? 0);
        });

        $response = $next($request);

        $totalMs = (microtime(true) - $startedAt) * 1000;

        Log::info('REQ_PROFILE', [
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'route' => optional($request->route())->getName(),
            'status' => $response->getStatusCode(),
            'total_ms' => round($totalMs, 2),
            'db_ms' => round($dbMs, 2),
            'db_queries' => $dbQueries,
        ]);

        return $response;
    }
}
