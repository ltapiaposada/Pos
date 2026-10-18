<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdministrationAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_routes_require_authentication(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/companies')->assertRedirect(route('login'));
        $this->get('/payments')->assertRedirect(route('login'));
    }

    public function test_platform_administrator_can_start_and_end_a_session(): void
    {
        User::factory()->create([
            'email' => 'platform@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $this->post(route('login.store'), [
            'email' => 'platform@example.test',
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('logout'))->assertRedirect(route('login'));
    }
}
