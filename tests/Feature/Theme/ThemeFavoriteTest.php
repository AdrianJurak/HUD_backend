<?php

namespace Tests\Feature\Theme;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ThemeFavoriteTest extends TestCase
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

    public function test_user_can_favorite_a_theme(): void
    {
        $request = $this->actingAs($this->user)->postJson('/api/v1/themes/' . $this->theme->hash_id . '/favorite');

        $request->assertStatus(201);

        $request->assertJson(['message' => 'Added to favorites', 'is_favorite' => true]);

        $this->assertDatabaseHas('theme_user', [
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id,
        ]);
    }

    public function test_user_can_unfavorite_a_theme(): void
    {
        $this->user->favoriteThemes()->attach($this->theme->id);

        $request = $this->actingAs($this->user)->postJson('/api/v1/themes/' . $this->theme->hash_id . '/favorite');

        $request->assertStatus(201);

        $request->assertJson(['message' => 'Removed from favorites', 'is_favorite' => false]);

        $this->assertDatabaseMissing('theme_user', [
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id,
        ]);
    }

    public function test_guest_cannot_favorite_a_theme(): void
    {
        $request = $this->postJson('/api/v1/themes/' . $this->theme->hash_id . '/favorite');

        $request->assertStatus(401);
    }
}
