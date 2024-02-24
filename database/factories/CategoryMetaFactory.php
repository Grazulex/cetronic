<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class CategoryMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->domainWord(),
            'is_color' => fake()->boolean(),
            'is_meta' => fake()->boolean(),
            'is_export' => fake()->boolean(),
            'is_variant' => fake()->boolean(),
            'is_choice' => fake()->boolean()
        ];
    }
}
