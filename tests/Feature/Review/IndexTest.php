<?php

namespace Tests\Feature\Review;

use App\Models\Review;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IndexTest extends TestCase
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

        Review::factory()->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id,
            'rating' => '4',
            'title' => 'Example Title',
            'comment' => 'Example Comment',
        ]);
    }

    public function test_user_can_view_reviews_with_correct_theme_id(): void
    {
        $request = $this->getJson('/api/v1/themes/' . $this->theme->hash_id . '/reviews');

        $request->assertStatus(200);
    }

    public function test_user_cannot_view_reviews_without_correct_theme_id(): void
    {
        $request = $this->getJson('/api/v1/themes/' . 1234 . '/reviews');

        $request->assertStatus(404);
    }
}
