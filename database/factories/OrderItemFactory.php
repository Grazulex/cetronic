<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Prices are given in euros: the OrderItem mutators store them in cents.
 *
 * @extends Factory<OrderItem>
 */
final class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'item_id' => Item::factory(),
            'name' => fake()->words(3, true),
            'quantity' => 1,
            'price_paid' => 10,
            'price_show' => 0,
        ];
    }
}
