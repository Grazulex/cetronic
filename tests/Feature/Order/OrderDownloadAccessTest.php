<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\User;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/*
 * An order's PDF and pictures zip show the customer's addresses, prices and
 * products: only that customer and the shop staff may download them.
 */

beforeEach(function (): void {
    $this->owner = User::factory()->create(['role' => 'customer']);
    $this->order = Order::factory()->create(['user_id' => $this->owner->id]);
});

/*
 * The order has no pictures, so an allowed zip download answers 404: what
 * matters here is that the request went past the access check.
 */
function assertAllowed(TestResponse $response): void
{
    expect($response->getStatusCode())->not->toBeIn([302, 403]);
}

dataset('downloads', ['pdf' => 'user_orders.pdf', 'zip' => 'user_orders.zip']);

it('sends a guest to the login page', function (string $route): void {
    get(route($route, $this->order))->assertRedirect(route('login'));
})->with('downloads');

it('refuses another customer', function (string $route): void {
    actingAs(User::factory()->create(['role' => 'customer']));

    get(route($route, $this->order))->assertForbidden();
})->with('downloads');

it('lets the customer download their own order', function (string $route): void {
    actingAs($this->owner);

    assertAllowed(get(route($route, $this->order)));
})->with('downloads');

it('lets admins and agents download any order', function (string $route, string $role): void {
    actingAs(User::factory()->create(['role' => $role]));

    assertAllowed(get(route($route, $this->order)));
})->with('downloads')->with(['admin', 'agent']);

it('answers not found for an order without pictures and leaves no zip behind', function (): void {
    actingAs($this->owner);

    get(route('user_orders.zip', $this->order))->assertNotFound();

    expect(glob(storage_path('app/order-zips/*.zip')))->toBe([]);
});
