<?php

namespace Tests\Feature\Flags;

use App\Models\Flag;
use App\Models\Review;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreTest extends TestCase
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

        $this->reportingUser = User::factory()->create([
            'name' => 'Reporting User',
            'email' => 'reporting@user.com',
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

    public function test_user_can_flag_another_user(): void
    {
        $payload = [
            'reported_user_id' => $this->user->hash_id,
            'reason' => 'example reason',
        ];

        $response = $this->actingAs($this->reportingUser)->postJson('/api/v1/flags', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('flags', ['reported_user_id' => $this->user->id, 'reason' => 'example reason']);
    }

    public function test_user_can_flag_a_theme(): void
    {
        $payload = [
            'theme_id' => $this->theme->hash_id,
            'reason' => 'example reason',
        ];

        $response = $this->actingAs($this->reportingUser)->postJson('/api/v1/flags', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('flags', ['theme_id' => $this->theme->id, 'reason' => 'example reason']);
    }

    public function test_user_can_flag_a_review(): void
    {
        $payload = [
            'review_id' => $this->review->hash_id,
            'reason' => 'example reason',
        ];

        $response = $this->actingAs($this->reportingUser)->postJson('/api/v1/flags', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('flags', ['review_id' => $this->review->id, 'reason' => 'example reason']);
    }

    public function test_guest_cannot_flag_anything(): void
    {
        $payload = [
            'reported_user_id' => $this->user->hash_id,
            'reason' => 'example reason',
        ];

        $response = $this->postJson('/api/v1/flags', $payload);

        $response->assertStatus(401);
    }

    public function test_user_cannot_flag_himself(): void
    {
        $payload = [
            'reported_user_id' => $this->user->hash_id,
            'reason' => 'example reason',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/flags', $payload);

        $response->assertStatus(422);

        $response->assertJson(['message' => 'You cannot report yourself']);
    }

    public function test_user_cannot_send_empty_request(): void
    {
        $payload = [];

        $response = $this->actingAs($this->reportingUser)->postJson('/api/v1/flags', $payload);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['theme_id',
            'reported_user_id',
            'review_id']);
    }

    public function test_user_cannot_send_a_flag_without_any_id(): void
    {
        $payload = [
            'reason' => 'example reason',
        ];

        $response = $this->actingAs($this->reportingUser)->postJson('/api/v1/flags', $payload);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['theme_id',
            'reported_user_id',
            'review_id']);
    }

    public function test_user_cannot_send_another_flag_with_exact_id(): void
    {
        Flag::create([
            'user_id' => $this->reportingUser->id,
            'theme_id' => $this->theme->id,
            'reason' => 'example reason',
        ]);

        $payload = [
            'theme_id' => $this->theme->hash_id,
            'reason' => 'example reason',
        ];

        $response = $this->actingAs($this->reportingUser)->postJson('/api/v1/flags', $payload);

        $response->assertStatus(422);
    }
}
