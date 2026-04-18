<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_all_categories()
    {
        $dark = Category::factory()->create(['name' => 'Dark'],);
        $light = Category::factory()->create(['name' => 'Light'],);

        $response = $this->get('api/v1/categories');

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                [
                    'id' => $dark->id,
                    'name' => 'Dark'
                ],
                [
                    'id' => $light->id,
                    'name' => 'Light'
                ]
            ]
        ]);
    }
}
