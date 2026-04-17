<?php

namespace Tests\Feature\Theme;

use App\Models\Category;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Theme $theme;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subSeconds(10)
        ]);

        $this->theme = Theme::factory()->create([
            'title' => 'Example Title',
            'description' => 'Example Description',
            'user_id' => $this->user->id,
            'layout_config' => ['Type'=>'Speedometer','Size'=>'Small'],
            'images' => ['themes\fake_image.jpg'],
        ]);
    }

    public function test_logged_in_user_can_update_a_theme(): void
    {
        Storage::fake('public');
        $files = collect()->times(5, function ($number) {
            return UploadedFile::fake()->image("new_{$number}.jpg");
        })->toArray();

        $dark = Category::factory()->create([
            'name' => 'dark',
        ]);

        $light = Category::factory()->create([
            'name' => 'light',
        ]);

        $this->theme->categories()->attach($dark->id);

        $payload = [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'layout_config' => ['Type'=>'Digital','Size'=>'Big'],
            'images' => $files,
            'categories' => [$light->id]
        ];


        $response = $this->actingAs($this->user)->putJson('/api/v1/themes/'.$this->theme->hash_id, $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('themes', [
            'title' => $payload['title'],
            'description' => $payload['description'],
        ]);

        $this->assertDatabaseMissing('themes', [
            'title' => $this->theme->title,
            'description' => $this->theme->description,
        ]);

        $this->assertDatabaseHas('category_theme', [
            'theme_id' => $response->json('id'),
            'category_id' => $light->id,
        ]);

        $this->assertDatabaseMissing('category_theme', [
            'theme_id' => $response->json('id'),
            'category_id' => $dark->id,
        ]);

        $images = $response->json('images');

        $this->assertNotEmpty($images);

        $this->assertCount(5, $images);

        foreach ($images as $imagePath) {
            Storage::disk('public')->assertExists($imagePath);
        }

        Storage::disk('public')->assertMissing($this->theme->images);
    }

    public function test_guest_user_cannot_update_a_theme(): void
    {
        $response = $this->putJson('/api/v1/themes/'.$this->theme->hash_id, [
            'title' => 'Sneaky Theme',
            'layout_config' => ['Type' => 'Basic']
        ]);

        $response->assertStatus(401);
    }

    public function test_user_not_owning_theme_cannot_update_a_theme(): void
    {
        $user2 = User::factory()->create([
            'name' => 'Test User',
            'email' => 'Test@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subSeconds(10)
        ]);

        $response = $this->actingAs($user2)->putJson('/api/v1/themes/'.$this->theme->hash_id, [
            'title' => 'Sneaky Theme',
            'layout_config' => ['Type' => 'Basic']
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_upload_more_than_5_images(): void
    {
        Storage::fake('public');
        $files = collect()->times(6, function ($number) {
            return UploadedFile::fake()->image("avatar_{$number}.jpg");
        })->toArray();

        $payload = [
            'title' => 'Theme with too many images',
            'layout_config' => ['Type'=>'Speedometer','Size'=>'Small'],
            'images' => $files,
        ];

        $response = $this->actingAs($this->user)->putJson('/api/v1/themes/'.$this->theme->hash_id, $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['images']);
    }

    #[DataProvider('incorrectFiles')]
    public function test_user_cannot_upload_invalid_files($filename, $size, $mimeType): void
    {
        Storage::fake('public');

        $invalidFile = UploadedFile::fake()->create($filename, $size, $mimeType);

        $payload = [
            'title' => 'Theme with too many images',
            'layout_config' => ['Type'=>'Speedometer','Size'=>'Small'],
            'images' => [$invalidFile],
        ];

        $response = $this->actingAs($this->user)->putJson('/api/v1/themes/'.$this->theme->hash_id, $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['images.0']);
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
                9000,
                'image/jpeg'
            ],
        ];
    }
}
