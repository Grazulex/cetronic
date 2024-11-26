<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+2 month'),
            'local' => $this->faker->randomElement(['fr', 'en', 'nl']),
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];
    }
}
