<?php

namespace Tests\Feature\Auth;

use App\Models\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/auth/register', $payload);

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('accessToken')
                ->has('refreshToken')
                ->etc()
            );

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'john@example.com', // Duplicate
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['accessToken', 'refreshToken']);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        // Assuming BadCredentialException renders as 400 or 401
        // Adjust status based on your exception handler
        $response->assertStatus(400);
    }

    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // We need to create a refresh token record manually or via logic
        // Since your AuthController logic creates it on login, let's login first
        $loginResponse = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password', // Assumes factory uses 'password'
        ]);

        $refreshToken = $loginResponse->json('refreshToken');

        // Now attempt refresh
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/auth/refresh', [
                'refreshToken' => $refreshToken
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['accessToken', 'refreshToken']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Login to generate the refresh token record
        $loginResponse = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $refreshToken = $loginResponse->json('refreshToken');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/auth/logout', [
                'refreshToken' => $refreshToken
            ]);

        $response->assertNoContent();

        // Ensure refresh token is gone from DB
        $this->assertDatabaseMissing('refresh_tokens', [
            'token' => $refreshToken
        ]);
    }
}
