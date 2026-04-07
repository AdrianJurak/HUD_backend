<?php

namespace Database\Factories;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Theme>
 */
class ThemeFactory extends Factory
{
    protected $model = Theme::class;
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-1 year', 'now');
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->city(),
            'description' => $this->faker->paragraph(3),
            'layout_config' => [
                [
                    'type' => 'speedometer',
                    'data' => [
                        'x' => 100,
                        'y' => 100,
                        'size' => 50,
                        'color' => '#32cd32',
                        'style' => 'digital'
                    ]
                ]
            ],
        ];
    }
}
