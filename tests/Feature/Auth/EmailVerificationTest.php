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

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_verify_email_with_correct_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'verification_token'=> '123456',
            'verification_token_expires_at' => now()->addSeconds(10),
        ]);

        $payload = [
            'email' => $user->email,
            'verification_token' => $user->verification_token,
        ];

        $response = $this->postJson('/api/v1/verify', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users',[
            'email' => $user->email,
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ]);

        $this->assertNotNull($user->fresh()->email_verified_at);

        $response->assertJsonStructure(['token']);
    }

    #[DataProvider('invalidFieldProvider')]
    public function test_user_can_not_verify_email_with_invalid_credentials($payload): void{
        User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'verification_token'=> '123456',
            'verification_token_expires_at' => now()->addSeconds(30)
        ]);

        $response = $this->postJson('/api/v1/verify', $payload);

        $response->assertStatus(400);
    }

    public static function invalidFieldProvider() :array
    {
        return [
            'Invalid email' => [
                ['email' => "test@user.com", 'verification_token'=> '123456'],
            ],
            'Invalid token' => [
                ['email' => 'example@user.com', 'verification_token' => '999999'],
            ]
        ];
    }

    #[DataProvider('invalidValidationProvider')]
    public function test_verification_validation_fails($payload,$invalidField): void
    {
        $response = $this->postJson('/api/v1/verify', $payload);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors($invalidField);
    }

    public static function invalidValidationProvider() :array
    {
        return [
            'Missing email' => [
                ['verification_token' => '123456'],
                'email'
            ],
            'Missing token' => [
                ['email' => 'example@user.com'],
                'verification_token'
            ]
        ];
    }

    public function test_user_cannot_verify_with_verified_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'verification_token'=> '123456',
            'verification_token_expires_at' => now()->addSeconds(30),
            'email_verified_at' => now()->subSeconds(10)
        ]);

        $payload = [
            'email' => $user->email,
            'verification_token' => $user->verification_token,
        ];

        $response = $this->postJson('/api/v1/verify', $payload);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Email already verified.']);
    }

    public function test_user_cannot_verify_with_expired_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'verification_token'=> '123456',
            'verification_token_expires_at' => now()->subSeconds(10),
        ]);

        $payload = [
            'email' => $user->email,
            'verification_token' => $user->verification_token,
        ];

        $response = $this->postJson('/api/v1/verify', $payload);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Verification code has expired.']);
    }

    public function test_user_can_refresh_verification_token_with_valid_credentials(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'verification_token'=> '123456',
            'verification_token_expires_at' => now()->subSeconds(30),
        ]);

        $payload = [
            'email' => $user->email,
        ];

        $response = $this->postJson('/api/v1/token-refresh', $payload);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Verification code sent.']);

        Mail::assertSent(VerificationCodeMail::class, function ($mail) use ($user) {
            $isEmailCorrect = $mail->hasTo($user->email);

            $freshUser = $user->fresh();

            $isCorrectToken = $mail->token === $freshUser->verification_token;

            return $isEmailCorrect && $isCorrectToken;
        });
    }

    public function test_verification_token_refresh_validation_fails(): void
    {
        $response = $this->postJson('/api/v1/token-refresh', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }


    public function test_user_cannot_refresh_verification_token_with_invalid_email(): void
    {
        User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'verification_token'=> '123456',
            'verification_token_expires_at' => now()->subSeconds(30),
        ]);

        $payload = [
            'email' => 'test@email.com',
        ];

        $response = $this->postJson('/api/v1/token-refresh', $payload);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'User not found.']);
    }

    public function test_user_cannot_refresh_verification_token_with_valid_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'verification_token'=> '123456',
            'verification_token_expires_at' => now()->addSeconds(30),
        ]);

        $payload = [
            'email' => $user->email,
        ];

        $response = $this->postJson('/api/v1/token-refresh', $payload);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Current code is still valid try again later']);
    }


}
