<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryTranslation>
 */
final class CategoryTranslationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->domainWord(),
            'slug' => fake()->slug(),
            'locale' => fake()->randomElement(['fr', 'nl'])
        ];
    }
}
