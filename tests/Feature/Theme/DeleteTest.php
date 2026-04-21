<?php

namespace Tests\Feature\Theme;

use App\Models\Category;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Theme $theme;

    protected array $imagePaths;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subSeconds(10)
        ]);

        $this->imagePaths = [];
        for ($i = 1; $i <= 3; $i++) {
            $path = "themes/fake_image_{$i}.jpg";
            Storage::disk('public')->put($path, 'test content');
            $this->imagePaths[] = $path;
        }

        $this->theme = Theme::factory()->create([
            'title' => 'Example Title',
            'description' => 'Example Description',
            'user_id' => $this->user->id,
            'layout_config' => ['Type'=>'Speedometer','Size'=>'Small'],
            'images' => $this->imagePaths,
        ]);

        $category = Category::factory()->create();
        $this->theme->categories()->attach($category->id);
    }

    public function test_owner_user_can_delete_the_theme(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/v1/themes/' . $this->theme->hash_id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('themes', ['id' => $this->theme->id]);

        $this->assertDatabaseEmpty('category_theme');

        foreach($this->imagePaths as $imagePath) {
            Storage::disk('public')->assertMissing($imagePath);
        }
    }

    public function test_non_owner_user_cannot_delete_the_theme(): void
    {
        $nonOwnerUser = User::factory()->create();

        $response = $this->actingAs($nonOwnerUser)->deleteJson('/api/v1/themes/' . $this->theme->hash_id);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_delete_the_theme(): void
    {
        $response = $this->deleteJson('/api/v1/themes/' . $this->theme->hash_id);

        $response->assertStatus(401);
    }
}
