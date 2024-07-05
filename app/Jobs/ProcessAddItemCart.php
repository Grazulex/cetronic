<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CartOut;
use App\Models\Item;
use App\Services\CartService;
use App\Services\ItemService;
use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessAddItemCart implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Item $item) {}

    public function handle(): void
    {
        $cartOuts = CartOut::where('item_id', $this->item->id)->get();

        foreach ($cartOuts as $cartOut) {
            $userService = new UserService(user: $cartOut->user);
            $cart = $userService->getOpenCart();
            $cartService = new CartService();
            if ( ! $cart) {
                $cart = $cartService->creatNewCart(user: $cartOut->user);
            }

            $itemService = new ItemService(item: $cartOut->item);
            $item = $itemService->getPrice(user: $cartOut->user);

            $cartService->addToCart(
                cart: $cart,
                item:  $this->item,
                price: $item['price_promo'],
                price_promo: $item['price_end'],
                quantity: $cartOut->quantity,
                price_old: $item['price_start'],
            );

            if ($cartOut->user->can_connect() && $cartOut->user->receive_cart_notification) {
                SendMailAddItemCart::dispatch(user: $cartOut->user, item: $this->item);
            }
            $cartOut->delete();
        }
    }
}
