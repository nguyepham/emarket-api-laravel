<?php

namespace Tests\Feature\User;

use App\Models\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/me/password', [
                'currentPassword' => 'OldPassword123!',
                'newPassword' => 'NewPassword456!',
                'newPassword_confirmation' => 'NewPassword456!',
            ]);

        $response->assertNoContent();

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword456!', $user->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/me/password', [
                'currentPassword' => 'WrongPassword!',
                'newPassword' => 'NewPassword456!',
                'newPassword_confirmation' => 'NewPassword456!',
            ]);

        // Expecting BadCredentialException (400) or similar
        $response->assertStatus(400);

        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $user->password));
    }
}
