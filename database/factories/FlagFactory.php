<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flag>
 */
class FlagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-1 year', 'now');
        return [
            'user_id' => null,
            'theme_id' => null,
            'review_id' => null,
            'reason' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement(['pending', 'resolved', 'rejected']),
        ];
    }
}
