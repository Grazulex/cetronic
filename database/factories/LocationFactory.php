<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'company' => fake()->company(),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'street' => fake()->streetAddress(),
            'street_number' => fake()->randomDigit(),
            'zip' => fake()->postcode(),
            'city' => fake()->city(),
            'country' => 'BEL',
            'phone' => fake()->phoneNumber(),
            'vat' => fake()->word(),
        ];
    }
}
