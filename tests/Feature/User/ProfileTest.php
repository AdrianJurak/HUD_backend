<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_their_profile_without_picture(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/user');

        $response->assertStatus(200);

        $response->assertJson([
            'id' => $user->hash_id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture_url' => null,
        ]);
    }

    public function test_user_can_get_their_profile_with_picture(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'profile_picture_url' => 'avatars/my-cool-avatar.jpg',
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/user');

        $response->assertStatus(200);

        $response->assertJson([
            'id' => $user->hash_id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture_url' => asset('storage/avatars/my-cool-avatar.jpg'),
        ]);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }

    public function test_user_can_update_their_profile(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $payload = [
            'name' => 'Test User',
            'profile_picture_url' => $file,
        ];

        $response = $this->actingAs($user)->putJson('/api/v1/profile', $payload);

        $response->assertStatus(200);

        $freshUser = $user->fresh();

        $this->assertEquals($payload['name'], $freshUser->name);

        Storage::disk('public')->assertExists($freshUser->profile_picture_url);
    }

    #[DataProvider('incorrectFiles')]
    public function test_user_cannot_update_their_profile_with_incorrect_file_format_or_incorrect_size($filename, $size, $mimeType): void
    {
        Storage::fake('public');

        $badFile = UploadedFile::fake()->create($filename, $size, $mimeType);

        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $payload = [
            'profile_picture_url' => $badFile
        ];

        $response = $this->actingAs($user)->putJson('/api/v1/profile', $payload);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors('profile_picture_url');
    }

    public static function incorrectFiles(): array
    {
        return [
            'Wrong Format' => [
                'document.pdf',
                100,
                'application/pdf'
            ],
            'File too big' => [
                'huge_avatar.jpg',
                5000,
                'image/jpeg'
            ],
        ];
    }
}
