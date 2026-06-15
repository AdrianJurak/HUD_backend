<?php

namespace Tests\Feature\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        Mail::fake();
        $payload = [
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/v1/register', $payload);

        $response->assertStatus(201);

        $response->assertJson([
            'email' => $payload['email'],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        $user = User::where('email', $payload['email'])->first();
        $this->assertNotNull($user->verification_token);

        Mail::assertQueued(VerificationCodeMail::class, function ($mail) use ($user) {
            $isEmailCorrect = $mail->hasTo($user->email);

            $isTokenCorrect = $mail->token === $user->verification_token;

            return $isEmailCorrect && $isTokenCorrect;
        });
    }

    #[DataProvider('invalidFieldProvider')]
    public function test_user_cannot_register_with_invalid_data($payload, $invalidField): void
    {
        $response = $this->postJson('/api/v1/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([$invalidField]);
    }

    public static function invalidFieldProvider(): array
    {
        return [
            'Missing name' => [
                ['email' => 'example@user.com', 'password' => 'password', 'password_confirmation' => 'password'],
                'name',
            ],
            'Missing email' => [
                ['name' => 'Example User', 'password' => 'password', 'password_confirmation' => 'password'],
                'email',
            ],
            'Missing password' => [
                ['name' => 'Example User', 'email' => 'example@user.com', 'password_confirmation' => 'password'],
                'password',
            ],
            'Missing password confirmation' => [
                ['name' => 'Example User', 'email' => 'example@user.com', 'password' => 'password'],
                'password',
            ],
        ];
    }
}
