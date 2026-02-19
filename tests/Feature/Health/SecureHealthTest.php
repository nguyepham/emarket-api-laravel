<?php

namespace Tests\Feature\Health;

use App\Models\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecureHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_liveness_probe(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/secure-health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonStructure(['status', 'time']);
    }

    public function test_guest_cannot_access_liveness_probe(): void
    {
        $response = $this->getJson('/secure-health');

        $response->assertStatus(401); // Unauthorized
    }
}
