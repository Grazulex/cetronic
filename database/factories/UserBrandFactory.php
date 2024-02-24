<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserBrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand_id' => Brand::inRandomOrder()->first()->id,
            'category_id' => Category::inRandomOrder()->first()->id,
            'reduction' => $this->faker->randomFloat(2, 0, 100),
            'coefficient' => $this->faker->randomFloat(2, 0, 3),
            'addition_price' => $this->faker->randomFloat(2, 0, 2),
            'price_type' => $this->faker->randomElement(PriceTypeEnum::cases()),
            'not_show_promo' => $this->faker->boolean,
            'is_excluded' => $this->faker->boolean,
        ];
    }
}
