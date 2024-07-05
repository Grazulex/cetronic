<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\CartOut\CreateCartOutAction;
use App\DataObjects\CartOut\CartOutDataObject;
use App\Enum\CartStatusEnum;
use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessRemoveItemCart implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Item $item) {}

    public function handle(): void
    {
        $cartItems = CartItem::where('item_id', $this->item->id)->whereHas('cart', function (Builder $q): void {
            $q->where('status', CartStatusEnum::OPEN->value);
        })->get();

        foreach ($cartItems as $cartItem) {
            (new CreateCartOutAction())->handle(
                new CartOutDataObject(
                    item: $this->item,
                    user: $cartItem->cart->user,
                    quantity: $cartItem->quantity,
                ),
            );

            if ($cartItem->cart->user->can_connect() && $cartItem->cart->user->receive_cart_notification) {
                SendMailRemoveItemCart::dispatch(user: $cartItem->cart->user, item: $this->item);
            }
            $cartItem->delete();
        }
    }
}
