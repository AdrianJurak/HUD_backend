<?php

namespace Tests\Feature\Theme;

use App\Models\Category;
use App\Models\Download;
use App\Models\Review;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_themes(): void
    {
        $user = User::factory()->create([
            'name' => 'Example User',
            'email' => 'example@user.com',
            'password' => Hash::make('password'),
        ]);
        $theme = Theme::factory()->create([
            'user_id' => $user->id,
            'title' => 'Example Title',
            'description' => 'Example Description',
            'layout_config' => ['test'],
            'images' => ['themes/fake_image.png'],
        ]);

        $response = $this->getJson('/api/v1/themes');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'images',
                    'likes_count',
                    'reviews_count',
                    'downloads_count',
                    'user' => [
                        'id',
                        'name',
                        'profile_picture_url'
                    ],
                    'categories' => [
                        'name'
                    ]
                ]
            ]
        ]);

        $response->assertJsonPath('data.0.user.name', $user->name);
        $response->assertJsonPath('data.0.title', $theme->title);
    }

    public function test_user_can_sort_themes_by_downloads():void
    {
        $weakTheme = Theme::factory()->create(['title' => 'Weak']);
        $hitTheme = Theme::factory()->create(['title' => 'Hit']);

        User::factory(6)->create();

        Download::factory()->count(1)->create([
            'theme_id' => $weakTheme->id,
        ]);

        Download::factory()->count(5)->create([
            'theme_id' => $hitTheme->id,
        ]);

        $response = $this->getJson('/api/v1/themes?sort=downloads');

        $response->assertStatus(200);

        $response->assertJsonPath('data.0.title','Hit');
        $response->assertJsonPath('data.1.title','Weak');

        $response->assertJsonPath('data.0.downloads_count',5);
    }

    public function test_user_can_sort_themes_by_reviews():void
    {
        $weakTheme = Theme::factory()->create(['title' => 'Weak']);
        $hitTheme = Theme::factory()->create(['title' => 'Hit']);

        Review::factory()->count(1)->create([
            'theme_id' => $weakTheme->id,
        ]);

        Review::factory()->count(5)->create([
            'theme_id' => $hitTheme->id,
        ]);

        $response = $this->getJson('/api/v1/themes?sort=reviews');

        $response->assertStatus(200);

        $response->assertJsonPath('data.0.title','Hit');
        $response->assertJsonPath('data.1.title','Weak');

        $response->assertJsonPath('data.0.reviews_count',5);
    }

    public function test_user_can_sort_themes_by_likes():void
    {
        $weakTheme = Theme::factory()->create(['title' => 'Weak']);
        $hitTheme = Theme::factory()->create(['title' => 'Hit']);

        $users = User::factory(6)->create();

        $weakTheme->favoritedBy()->attach($users->pluck('id')->first());
        $hitTheme->favoritedBy()->attach($users->pluck('id')->skip(1)->take(5));

        $response = $this->getJson('/api/v1/themes?sort=likes');

        $response->assertStatus(200);

        $response->assertJsonPath('data.0.title','Hit');
        $response->assertJsonPath('data.1.title','Weak');

        $response->assertJsonPath('data.0.likes_count',5);
    }

    public function test_user_can_search_themes_by_title():void
    {
        Theme::factory()->create(['title' => 'Second']);
        Theme::factory()->create(['title' => 'First']);

        $response = $this->getJson('/api/v1/themes?search=first');

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.title','First');
    }

    public function test_user_can_search_themes_by_description():void
    {
        Theme::factory()->create(['title'=>'Wrong one','description' => 'Second']);
        Theme::factory()->create(['title'=>'Correct one','description' => 'First']);

        $response = $this->getJson('/api/v1/themes?search=first');

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.title','Correct one');
    }

    public function test_user_can_search_by_multiple_categories():void
    {
        $firstTheme = Theme::factory()->create(['title'=>'Dark',]);
        $secondTheme = Theme::factory()->create(['title'=>'Light',]);
        Theme::factory()->create(['title'=>'None',]);

        $dark = Category::factory()->create(['name'=>'Dark',]);
        $light = Category::factory()->create(['name'=>'Light',]);

        $firstTheme->categories()->attach($dark);
        $secondTheme->categories()->attach($light);

        $response = $this->getJson('/api/v1/themes?categories=Dark,Light');

        $response->assertStatus(200);

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.title','Dark');
    }

    public function test_user_can_search_by_one_category():void
    {
        $firstTheme = Theme::factory()->create(['title'=>'Dark',]);
        $secondTheme = Theme::factory()->create(['title'=>'Light',]);

        $dark = Category::factory()->create(['name'=>'Dark',]);
        $light = Category::factory()->create(['name'=>'Light',]);

        $firstTheme->categories()->attach($dark);
        $secondTheme->categories()->attach($light);

        $response = $this->getJson('/api/v1/themes?categories=Dark');

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.title','Dark');
    }

    public function test_user_receives_empty_list_searching_by_non_existing_category():void
    {
        $response = $this->getJson('/api/v1/themes?categories=Dark');

        $response->assertStatus(200);

        $response->assertJsonCount(0, 'data');
    }

    public function test_user_can_view_favorited_themes_while_logged_in():void
    {
        $theme = Theme::factory()->create(['title'=>'Favorited']);
        Theme::factory()->create(['title'=>'Not favorited']);

        $user = User::factory()->create();

        $theme->favoritedBy()->attach($user->id);

        $response = $this->actingAs($user)->getJson('/api/v1/themes?favorites=1');

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title','Favorited');
    }

    public function test_user_cannot_search_favorited_themes_while_not_logged_in():void
    {
        $response = $this->getJson('/api/v1/themes?favorites=1');

        $response->assertStatus(401);
    }

    public function test_user_cannot_search_themes_by_non_existent_title():void
    {
        Theme::factory()->create(['title'=>'Test']);

        $response = $this->getJson('/api/v1/themes?search=example');

        $response->assertStatus(200);

        $response->assertJsonCount(0, 'data');
    }


}
