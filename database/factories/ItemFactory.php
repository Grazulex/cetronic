<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reference = fake()->unique()->randomNumber(6);
        $brand_id = Brand::all()->random()->id;
        $category_id = Category::all()->random()->id;

        return [
            'reference' => $reference,
            'slug' => '',
            'brand_id' => $brand_id,
            'category_id' => $category_id,
            'description' => fake()->paragraph(),
            'is_published' => true,
            'is_new' => fake()->boolean(),
            'price' => fake()->randomNumber(2, true),
            'price_b2b' => fake()->randomNumber(2, true),
            'price_promo' => fake()->randomNumber(2, true),
            'price_special1' => fake()->randomNumber(2, true),
            'price_special2' => fake()->randomNumber(2, true),
            'price_special3' => fake()->randomNumber(2, true),
            'multiple_quantity' => fake()->randomNumber(1),
        ];
    }
}
