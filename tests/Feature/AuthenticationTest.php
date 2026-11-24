<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_valid_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@mayora.com',
            'password' => 'password123',
            'role' => 'operator',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'operator@mayora.com',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_invalid_login_returns_401(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'missing@mayora.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_request_has_no_access(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
