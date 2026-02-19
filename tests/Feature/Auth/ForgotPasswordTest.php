<?php

namespace Tests\Feature\Auth;

use App\Models\Auth\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_notification_for_valid_email()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/auth/password/forgot', [
            'email' => 'test@example.com',
        ]);

        $response->assertNoContent();

        Notification::assertSentTo(
            [$user],
            ResetPasswordNotification::class
        );
    }

    public function test_forgot_password_returns_204_even_if_email_not_found()
    {
        Notification::fake();

        $response = $this->postJson('/auth/password/forgot', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertNoContent();

        Notification::assertNothingSent();
    }
}
