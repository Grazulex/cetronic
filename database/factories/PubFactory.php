<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pub;
use Illuminate\Database\Eloquent\Factories\Factory;

class PubFactory extends Factory
{
    protected $model = Pub::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'title' => $this->faker->word,
            'type' => $this->faker->randomElement(['pub_1', 'pub_2', 'pub_3', 'pub_4', 'pub_5']),
            'language' => $this->faker->randomElement(['en','nl','fr']),
            'url' => $this->faker->url,
            'picture' => $this->faker->imageUrl(640, 480, 'animals', true),
        ];
    }
}
