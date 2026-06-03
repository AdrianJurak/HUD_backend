<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use App\Models\Theme;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//         User::factory(10)->has(Theme::factory()->count(3)->has(Review::factory()->count(3)))->create();
//
//         Category::factory(10)->create();

        $admin = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Adrian Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $categories = ['Telemetria', 'Interfejs', 'Nawigacja', 'Multimedia', 'Diagnostyka OBD'];

        foreach ($categories as $categoryName) {
            Category::create([
                'name' => $categoryName,
            ]);
        }

        Theme::create([
            'user_id' => $admin->id,
            'title' => 'Sportowy HUD (Czerwony Akcent)',
            'description' => 'Motyw z centralnym obrotomierzem i dużym wskaźnikiem doładowania turbo. Idealny do dynamicznej jazdy.',
            'layout_config' => json_encode(['position' => 'center', 'color_scheme' => 'red', 'widgets' => ['rpm', 'boost', 'speed']]),
            'images' => json_encode(['preview1.jpg', 'preview2.jpg']),
        ]);

        Theme::create([
            'user_id' => $admin->id,
            'title' => 'Minimalistyczna Telemetria',
            'description' => 'Czysty interfejs skupiony wyłącznie na ekonomii spalania i temperaturze płynów.',
            'layout_config' => json_encode(['position' => 'bottom', 'color_scheme' => 'blue', 'widgets' => ['temp', 'fuel', 'speed']]),
            'images' => json_encode([]),
        ]);
    }
}
