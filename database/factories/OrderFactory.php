<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'customer']),
            'status' => OrderStatusEnum::OPEN->value,
            'reference' => 'ORD-' . Str::upper(Str::random(10)),
            'shipping_street' => fake()->streetName(),
            'shipping_city' => fake()->city(),
            'shipping_zip' => fake()->postcode(),
            'shipping_country' => 'BEL',
            'invoice_street' => fake()->streetName(),
            'invoice_city' => fake()->city(),
            'invoice_zip' => fake()->postcode(),
            'invoice_country' => 'BEL',
        ];
    }
}
