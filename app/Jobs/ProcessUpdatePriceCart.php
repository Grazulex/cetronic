<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\CartStatusEnum;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\User;
use App\Services\ItemService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessUpdatePriceCart implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Item $item, public ?User $user = null)
    {
    }

    public function handle(): void
    {
        if ($this->user) {
            $cartItems = CartItem::where('item_id', $this->item->id)->whereHas('cart', function (Builder $q): void {
                $q->where('status', CartStatusEnum::OPEN->value)
                    ->where('user_id', $this->user->id);
            })->get();
        } else {
            $cartItems = CartItem::where('item_id', $this->item->id)->whereHas('cart', function (Builder $q): void {
                $q->where('status', CartStatusEnum::OPEN->value);
            })->get();
        }

        foreach ($cartItems as $cartItem) {
            $cart = $cartItem->cart;

            $itemService = new ItemService(item: $this->item);
            $item = $itemService->getPrice(user: $cartItem->cart->user);

            $cartItem->price = $item['price_end'];
            $cartItem->price_old = $item['price_start'];
            $cartItem->price_promo = $item['price_promo'];
            $cartItem->save();

            if ($cart->user->can_connect() && $cart->user->receive_cart_notification) {
                SendMailUpdateItemCart::dispatch(user: $cart->user, item: $this->item);
            }
            //$cartItem->delete();
        }
    }
}
