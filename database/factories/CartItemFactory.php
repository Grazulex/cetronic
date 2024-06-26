<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::all()->random()->id,
            'quantity' => fake()->numberBetween(1, 10),
            'price' => fake()->numberBetween(100, 1000),
            'price_promo' => fake()->numberBetween(100, 1000),
        ];
    }
}
