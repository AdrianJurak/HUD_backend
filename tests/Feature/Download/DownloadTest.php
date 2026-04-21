<?php

namespace Tests\Feature\Download;

use App\Models\Download;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DownloadTest extends TestCase
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

    public function test_user_can_download_a_theme(): void
    {
        $request = $this->actingAs($this->user)->postJson('/api/v1/themes/' . $this->theme->hash_id . '/download');

        $request->assertStatus(201);

        $request->assertJson(['message' => 'Theme downloaded']);

        $this->assertDatabaseHas('downloads', [
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id,
        ]);
    }

    public function test_user_cannot_redownload_a_theme(): void
    {
        Download::factory()->create([
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id,
        ]);

        $request = $this->actingAs($this->user)->postJson('/api/v1/themes/' . $this->theme->hash_id . '/download');

        $request->assertStatus(200);

        $request->assertJson(['message' => 'Theme already downloaded']);

        $this->assertDatabaseHas('downloads', [
            'user_id' => $this->user->id,
            'theme_id' => $this->theme->id,
        ]);
    }

    public function test_guest_cannot_download_a_theme(): void
    {
        $request = $this->postJson('/api/v1/themes/' . $this->theme->hash_id . '/download');

        $request->assertStatus(401);
    }
}
