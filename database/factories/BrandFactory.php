<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        File::makeDirectory('public/storage/brands', 0777, true, true);

        return [
            'name' => $name,
            'slug' => '',
            'description' => fake()->paragraph(),
            'is_published' => true,
            'is_featured' => true,
            'picture' => 'brands/'.fake()->image('public/storage/brands', 640, 480, null, false),
        ];
    }
}
