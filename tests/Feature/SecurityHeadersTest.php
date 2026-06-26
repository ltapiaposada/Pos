<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_security_headers(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none')
            ->assertHeader(
                'Permissions-Policy',
                'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()'
            );
    }

    public function test_remote_scanner_allows_same_origin_camera_only(): void
    {
        $token = 'security-camera-test';
        Cache::put("pos_scanner_session:{$token}", [
            'user_id' => 1,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ], now()->addMinutes(5));

        $this->get(route('pos.scanner.remote', ['token' => $token]))
            ->assertOk()
            ->assertHeader(
                'Permissions-Policy',
                'accelerometer=(), camera=(self), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()'
            );
    }

    public function test_product_create_allows_same_origin_camera(): void
    {
        $this->seed();
        $user = \App\Models\User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('products.create'))
            ->assertOk()
            ->assertHeader(
                'Permissions-Policy',
                'accelerometer=(), camera=(self), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()'
            )
            ->assertSee('open-product-barcode-scanner', false)
            ->assertSee('open-product-remote-scanner', false)
            ->assertSee('product-remote-scanner-modal', false);
    }

    public function test_https_proxy_generates_https_asset_urls(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders([
                'X-Forwarded-Host' => 'example-tunnel.trycloudflare.com',
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Port' => '443',
            ])
            ->get('/login')
            ->assertOk()
            ->assertSee('https://example-tunnel.trycloudflare.com/build/assets/', false)
            ->assertDontSee('http://example-tunnel.trycloudflare.com/build/assets/', false);
    }
}
