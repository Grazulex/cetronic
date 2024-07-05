<?php

declare(strict_types=1);

namespace App\DataObjects\Cart;

use App\Enum\CartStatusEnum;
use App\Models\Location;
use App\Models\User;

final readonly class CartDataObject
{
    public function __construct(
        private User            $user,
        private ?CartStatusEnum $status = CartStatusEnum::OPEN,
        private ?Location       $shipping_location = null,
        private ?Location       $invoice_location = null,
        private ?string         $cookie = null,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->user->id,
            'status' => $this->status,
            'shipping_location_id' => ($this->shipping_location) ? $this->shipping_location->id : null,
            'invoice_location_id' => ($this->invoice_location) ? $this->invoice_location->id : null,
            'cookie' => ($this->cookie) ?: null,
        ];
    }
}
