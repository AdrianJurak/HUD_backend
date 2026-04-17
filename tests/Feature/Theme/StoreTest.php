<?php

namespace Tests\Feature\Theme;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subSeconds(10)
        ]);
    }

    public function test_logged_in_user_can_create_a_theme(): void
    {
        Storage::fake('public');
        $files = collect()->times(5, function ($number) {
            return UploadedFile::fake()->image("avatar_{$number}.jpg");
        })->toArray();

        $dark = Category::factory()->create([
            'name' => 'dark',
        ]);

        $payload = [
            'title' => 'Example Title',
            'description' => 'Example Description',
            'layout_config' => ['Type'=>'Speedometer','Size'=>'Small'],
            'images' => $files,
            'categories' => [$dark->id]
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/themes', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('themes', [
            'title' => 'Example Title',
            'description' => 'Example Description',
        ]);

        $this->assertDatabaseHas('category_theme', [
            'theme_id' => $response->json('id'),
            'category_id' => $dark->id,
        ]);

        $images = $response->json('images');

        $this->assertNotEmpty($images);

        $this->assertCount(5, $images);

        foreach ($images as $imagePath) {
            Storage::disk('public')->assertExists($imagePath);
        }
    }

    public function test_guest_user_cannot_create_a_theme(): void
    {
        $response = $this->postJson('/api/v1/themes', [
            'title' => 'Sneaky Theme',
            'layout_config' => ['Type' => 'Basic']
        ]);

        $response->assertStatus(401);
    }

    #[DataProvider('invalidFieldProvider')]
    public function test_user_cannot_create_a_theme_with_invalid_data($payload, $invalidField): void
    {

        $response = $this->actingAs($this->user)->postJson('/api/v1/themes', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([$invalidField]);
    }

    public static function invalidFieldProvider(): array
    {
        return [
            'Missing title' => [
                ['layout_config'=>['Type'=>'Speedometer','Size'=>'Small']],
                'title'
            ],
            'Missing layout_config' => [
                ['title'=>'Test Title'],
                'layout_config'

            ]
        ];
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

        $response = $this->actingAs($this->user)->postJson('/api/v1/themes', $payload);

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

        $response = $this->actingAs($this->user)->postJson('/api/v1/themes', $payload);

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
