<?php
namespace Tests\Feature\Auth;
use App\Models\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PasswordForgotReproductionTest extends TestCase {
    use RefreshDatabase;
    public function test_forgot_password_sends_link() {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $response = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'test@example.com']);
        if ($response->status() === 404) {
             $response = $this->postJson('/auth/password/forgot', ['email' => 'test@example.com']);
        }
        $response->assertNoContent();
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'test@example.com']);
    }
}
