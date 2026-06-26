<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@pos.test')->firstOrFail();
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }
}
