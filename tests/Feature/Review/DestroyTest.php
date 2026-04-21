<?php

namespace Tests\Feature\Review;

use App\Models\Review;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Theme $theme;

    protected Review $review;

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

        $this->review = Review::factory()->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id,
            'rating' => '4',
            'title' => 'Example Title',
            'comment' => 'Example Comment',
        ]);
    }

    public function test_user_can_delete_review(): void
    {
        $request = $this->actingAs($this->user)
            ->deleteJson('/api/v1/themes/' . $this->theme->hash_id . '/reviews/' . $this->review->hash_id);

        $request->assertStatus(200);

        $this->assertModelMissing($this->review);
    }

    public function test_user_cannot_delete_another_users_review(): void
    {
        $user2 = User::factory()->create();

        $request = $this->actingAs($user2)
            ->deleteJson('/api/v1/themes/' . $this->theme->hash_id . '/reviews/' . $this->review->hash_id);

        $request->assertStatus(403);
    }

    public function test_user_cannot_delete_review_with_invalid_theme_id(): void
    {
        $request = $this->actingAs($this->user)
            ->deleteJson('/api/v1/themes/'. 1234 . '/reviews/' . $this->review->hash_id);

        $request->assertStatus(404);
    }

    public function test_user_cannot_delete_review_with_invalid_review_id(): void
    {
        $request = $this->actingAs($this->user)
            ->deleteJson('/api/v1/themes/'. $this->theme->hash_id . '/reviews/' . 1234);

        $request->assertStatus(404);
    }
}
