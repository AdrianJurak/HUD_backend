<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-1 years', 'now');
        return [
            'name'=>$this->faker->randomElement(['dark', 'race','minimalist','airplane mode'])
        ];
    }
}
