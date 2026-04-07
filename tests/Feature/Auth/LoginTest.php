<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $payload = [
            'email' => 'example@user.com',
            'password' => 'password'
        ];

        User::factory()->create([
            'name' => 'Example User',
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/login', $payload);

        $response->assertStatus(200);

        $token = $response->json('token');

        $authResponse = $this->withToken($token)->getJson('/api/v1/user');

        $authResponse->assertStatus(200);
    }

    #[DataProvider('invalidValidationProvider')]
    public function test_login_validation_fails($payload,$invalidField): void
    {

        $response = $this->postJson('/api/v1/login', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($invalidField);
    }

    public static function invalidValidationProvider(): array
    {
        return [
            'Missing email' => [
                ['password' => 'password'],
                'email'
            ],
            'Missing password' => [
                ['email' => 'example@user.com'],
                'password'
            ]
        ];
    }

    #[DataProvider('invalidFieldProvider')]
    public function test_user_cannot_login_with_incorrect_credentials($payload): void
    {
        User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/login', $payload);

        $response->assertStatus(401);

        $response->assertJson(['error' => 'Your credentials are incorrect']);
    }

    public static function invalidFieldProvider(): array
    {
        return [
            'Invalid email' => [
                ['email' => "test@user.com", 'password' => 'password']
            ],
            'Invalid password' => [
                ['email' => 'example@user.com', 'password' => 'pancakes'],
            ]
        ];
    }

    public function test_user_cannot_login_with_unverified_email(): void
    {
        $payload = [
            'email' => 'example@user.com',
            'password' => 'password'
        ];

        User::factory()->create([
            'name' => 'Example User',
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
        ]);

        $response = $this->postJson('/api/v1/login', $payload);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Email is not verified.']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('test-app')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/logout');

        $response->assertStatus(200);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

}
