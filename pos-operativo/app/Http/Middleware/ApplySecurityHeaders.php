<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $cameraPolicy = $request->routeIs('pos.index', 'pos.scanner.remote', 'products.create', 'products.edit')
            ? 'camera=(self)'
            : 'camera=()';

        $response->headers->set(
            'Permissions-Policy',
            "accelerometer=(), {$cameraPolicy}, geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()"
        );

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
