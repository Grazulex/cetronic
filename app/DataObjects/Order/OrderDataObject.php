<?php

declare(strict_types=1);

namespace App\DataObjects\Order;

use App\Enum\OrderStatusEnum;
use App\Models\Cart;
use App\Services\CartService;
use App\Services\OrderService;

use function PHPUnit\Framework\isNull;

final readonly class OrderDataObject
{
    public function __construct(
        private Cart $cart,
    ) {
    }

    public function toArray(): array
    {
        $cartService = new CartService();
        $orderService = new OrderService();

        return [
            'reference' => $orderService->getNewOrderReference(),
            'comment' => $this->cart->comment,
            'franco' => $this->cart->user->franco,
            'user_id' => $this->cart->user_id,
            'agent_id' => ( ! isNull($this->cart->user->agent) ? $this->cart->user->agent->id : null),
            'status' => OrderStatusEnum::OPEN,
            'shipping_company' => $this->cart->shippingLocation->company,
            'shipping_name' => trim(
                $this->cart->shippingLocation->firstname.
                   ' '.
                   $this->cart->shippingLocation->lastname
            ),
            'shipping_street' => $this->cart->shippingLocation->street,
            'shipping_street_number' => $this->cart->shippingLocation->street_number,
            'shipping_street_other' => $this->cart->shippingLocation->street_other,
            'shipping_zip' => $this->cart->shippingLocation->zip,
            'shipping_city' => $this->cart->shippingLocation->city,
            'shipping_country' => $this->cart->shippingLocation->country,
            'shipping_phone' => $this->cart->shippingLocation->phone,
            'invoice_company' => $this->cart->invoiceLocation->company,
            'invoice_name' => $this->cart->invoiceLocation->firstname.
               ' '.
               $this->cart->invoiceLocation->lastname,
            'invoice_street' => $this->cart->invoiceLocation->street,
            'invoice_street_number' => $this->cart->invoiceLocation->street_number,
            'invoice_street_other' => $this->cart->invoiceLocation->street_other,
            'invoice_zip' => $this->cart->invoiceLocation->zip,
            'invoice_city' => $this->cart->invoiceLocation->city,
            'invoice_country' => $this->cart->invoiceLocation->country,
            'invoice_vat' => $this->cart->invoiceLocation->vat,
            'invoice_email' => $this->cart->user->email,
            'total_price' => $cartService->getTotal($this->cart) +
               $cartService->getShippingTotal(),
            'total_price_with_tax' => $cartService->getTotalVAT($this->cart) +
               $cartService->getShippingTotalVAT(),
            'total_tax' => $cartService->getVAT($this->cart)+$cartService->getShippingVAT(),
            'total_shipping' => $cartService->getShippingTotal(),
            'total_shipping_with_tax' => $cartService->getShippingTotalVAT(),
            'total_shipping_tax' => $cartService->getShippingVAT(),
            'total_products' => $cartService->getTotal($this->cart),
            'total_products_with_tax' => $cartService->getTotalVAT($this->cart),
            'total_products_tax' => $cartService->getVAT($this->cart),
            'discount' => $cartService->getDiscount($this->cart),
        ];
    }
}
