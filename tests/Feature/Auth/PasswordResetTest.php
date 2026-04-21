<?php

namespace Tests\Feature\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_reset_password_token_with_valid_credentials(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'verification_token_expires_at' => now()->subSeconds(10),
        ]);

        $payload = [
            'email' => $user->email,
        ];

        $response = $this->postJson('/api/v1/generate-password-token', $payload);

        $response->assertStatus(200);

        $response->assertJson(['message' => 'Verification code sent.']);

        $freshUser = $user->fresh();

        $this->assertNotNull($freshUser->verification_token_expires_at);
        $this->assertTrue($freshUser->verification_token_expires_at->isFuture());

        Mail::assertSent(VerificationCodeMail::class, function ($mail) use ($freshUser) {
            $isEmailCorrect = $mail->hasTo($freshUser->email);

            $isCorrectToken = $mail->token === $freshUser->verification_token;

            return $isEmailCorrect && $isCorrectToken;
        });
    }


    public function test_user_cannot_send_reset_password_token_with_invalid_credentials(): void
    {
        User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'verification_token_expires_at' => now()->subSeconds(10),
        ]);

        $payload = [
            'email' => 'test@user.com'
        ];

        $response = $this->postJson('/api/v1/generate-password-token', $payload);

        $response->assertStatus(404);

        $response->assertJson(['message' => 'User not found.']);
    }

    public function test_reset_password_token_validation(): void
    {
        $response = $this->postJson('/api/v1/generate-password-token', []); //missing email

        $response->assertStatus(422);

        $response->assertJsonValidationErrors('email');
    }

    public function test_user_cannot_send_reset_password_token_with_unverified_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
            'verification_token_expires_at' => now()->subSeconds(10),
        ]);

        $payload = [
            'email' => $user->email,
        ];

        $response = $this->postJson('/api/v1/generate-password-token', $payload);

        $response->assertStatus(400);

        $response->assertJson(['message' => 'Email is unverified.']);
    }

    public function test_user_cannot_send_reset_password_token_with_unexpired_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'verification_token_expires_at' => now()->addSeconds(10),
        ]);

        $payload = [
            'email' => $user->email,
        ];

        $response = $this->postJson('/api/v1/generate-password-token', $payload);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Previous token is still valid']);
    }

    public function test_user_can_change_password_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subSeconds(10),
            'verification_token' => '123456',
            'verification_token_expires_at' => now()->addSeconds(10),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'pancakes',
            'verification_token' => '123456',
        ];

        $response = $this->postJson('/api/v1/password-change', $payload);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Password updated successfully.']);

        $freshUser = $user->fresh();

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ]);

        $this->assertTrue(Hash::check($payload['password'], $freshUser->password));
    }

    #[DataProvider('invalidValidationProvider')]
    public function test_change_password_validation($payload,$invalidField): void
    {
        $response = $this->postJson('/api/v1/password-change', $payload);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors($invalidField);
    }

    public static function invalidValidationProvider(){
        return [
            'Missing email' => [
                ['verification_token' => '123456', 'password' => 'password'],
                'email'
            ],
            'Missing token' => [
                ['email' => 'example@user.com', 'password' => 'password'],
                'verification_token'
            ],
            'Missing password' => [
                ['email' => 'example@user.com','verification_token' => '123456'],
                'password'
            ]
        ];
    }

    public function test_user_cannot_change_password_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subSeconds(10),
            'verification_token' => '123456',
            'verification_token_expires_at' => now()->addSeconds(10),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'pancakes',
            'verification_token' => '999999',
        ];

        $response = $this->postJson('/api/v1/password-change', $payload);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Invalid verification code.']);
    }

    public function test_user_cannot_change_password_with_invalid_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subSeconds(10),
            'verification_token' => '123456',
            'verification_token_expires_at' => now()->addSeconds(10),
        ]);

        $payload = [
            'email' => 'test@user.com',
            'password' => 'pancakes',
            'verification_token' => '123456',
        ];

        $response = $this->postJson('/api/v1/password-change', $payload);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'User not found.']);
    }

    public function test_user_cannot_change_password_with_expired_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subSeconds(10),
            'verification_token' => '123456',
            'verification_token_expires_at' => now()->subSeconds(10),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'pancakes',
            'verification_token' => '123456',
        ];

        $response = $this->postJson('/api/v1/password-change', $payload);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Previous verification code is expired.']);
    }

}
