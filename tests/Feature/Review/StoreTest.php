<?php

namespace Tests\Feature\Review;

use App\Models\Review;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoreTest extends TestCase
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
            'layout_config' => ['Type' => 'Speedometer', 'Size' => 'Small'],
            'images' => ['themes\fake_image.jpg'],
        ]);
    }

    public function test_user_can_review_a_theme(): void
    {
        $payload = [
            'rating' => 4,
            'title' => 'Example Title',
            'comment' => 'Example Comment',
        ];

        $request = $this->actingAs($this->user)->postJson('/api/v1/themes/' . $this->theme->hash_id . '/reviews', $payload);

        $request->assertStatus(201);

        $this->assertDatabaseHas('reviews', $payload);
    }

    public function test_guest_cannot_review_a_theme(): void
    {
        $request = $this->postJson('/api/v1/themes/' . $this->theme->hash_id . '/reviews');
        $request->assertStatus(401);
    }

    public function test_user_can_update_his_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id,
            'rating' => '4',
            'title' => 'Example Title',
            'comment' => 'Example Comment',
        ]);

        $payload = [
            'rating' => 3,
            'title' => 'Test Title',
            'comment' => 'Test Comment',
        ];

        $this->assertDatabaseHas('reviews', ['title' => $review->title]);

        $request = $this->actingAs($this->user)->postJson('/api/v1/themes/' . $this->theme->hash_id . '/reviews', $payload);

        $request->assertStatus(201);

        $this->assertDatabaseHas('reviews', $payload);

        $this->assertDatabaseMissing('reviews', ['title' => $review->title]);
    }

    public function test_user_cannot_create_a_review_with_invalid_data(): void
    {
        $payload = [];

        $response = $this->actingAs($this->user)->postJson('/api/v1/themes/' . $this->theme->hash_id . '/reviews', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);
    }
}
