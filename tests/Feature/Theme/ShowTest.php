<?php

namespace Tests\Feature\Theme;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_theme_with_correct_id(): void
    {
        $user = User::factory()->create();

        $theme = Theme::factory()->create([
            'user_id' => $user->id,
            'title' => 'Example Title',
            'description' => 'Example Description',
            'layout_config' => ['test'],
            'images' => ['themes/fake_image.png'],
        ]);

        $response = $this->getJson('/api/v1/themes/' . $theme->hash_id);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'layout_config',
                'images',
                'likes_count',
                'reviews_count',
                'downloads_count',
                'user' => [
                    'id',
                    'name',
                    'profile_picture_url'
                ],
                'categories' => []
            ]

        ]);

        $response->assertJsonPath('data.id', $theme->hash_id);
    }

    public function test_user_cannot_see_theme_with_incorrect_id(): void
    {
        $response = $this->getJson('/api/v1/themes/999');

        $response->assertStatus(404);
    }
}
