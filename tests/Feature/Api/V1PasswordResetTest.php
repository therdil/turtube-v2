<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class V1PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC_MESSAGE = 'Bu e-posta adresi kayıtlıysa, şifre sıfırlama bağlantısı gönderilmiştir.';

    public function test_existing_and_unknown_email_addresses_receive_the_same_public_response(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'existing@example.test']);

        $existing = $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email]);
        $unknown = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'unknown@example.test']);

        $existing->assertOk()->assertExactJson(['message' => self::GENERIC_MESSAGE]);
        $unknown->assertOk()->assertExactJson(['message' => self::GENERIC_MESSAGE]);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_requires_a_valid_email_address(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_forgot_password_requests_are_rate_limited(): void
    {
        Notification::fake();

        $payload = ['email' => 'rate-limited@example.test'];

        foreach (range(1, 5) as $_) {
            $this->postJson('/api/v1/auth/forgot-password', $payload)
                ->assertOk()
                ->assertExactJson(['message' => self::GENERIC_MESSAGE]);
        }

        $this->postJson('/api/v1/auth/forgot-password', $payload)
            ->assertStatus(429);
    }
}
