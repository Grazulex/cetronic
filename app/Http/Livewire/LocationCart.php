<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Enum\LocationTypeEnum;
use App\Models\Cart;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

final class LocationCart extends Component
{
    public Cart $cart;

    public int $locationInvoice;

    public int $locationShipping;

    public string $comment;

    protected $rules = [
        'locationInvoice' => 'required',
        'locationShipping' => 'required',
        'comment' => 'nullable|string|max:255',
    ];

    public function mount(): void
    {
        $this->locationInvoice = ($this->cart->invoice_location_id) ? $this->cart->invoice_location_id : 0;
        $this->locationShipping = ($this->cart->shipping_location_id) ? $this->cart->shipping_location_id : 0;
        $this->comment = ($this->cart->comment) ? $this->cart->comment : '';
    }

    public function updatedLocationInvoice(): void
    {
        $this->validateOnly(field: 'locationInvoice');
        $this->cart->invoice_location_id = $this->locationInvoice;
        $this->cart->save();

        $this->emit(event: 'total_updated');

        $this->dispatchBrowserEvent(event: 'alert', data: [
            'type' => 'success',
            'message' => __('toast.location.updated'),
        ]);
    }

    public function updatedLocationShipping(): void
    {
        $this->validateOnly(field: 'locationShipping');
        $this->cart->shipping_location_id = $this->locationShipping;
        $this->cart->save();

        $this->emit(event: 'total_updated');

        $this->dispatchBrowserEvent(event: 'alert', data: [
            'type' => 'success',
            'message' => __('toast.location.updated'),
        ]);
    }

    public function updatedComment(): void
    {
        $this->validateOnly(field: 'comment');
        $this->cart->comment = $this->comment;
        $this->cart->save();

        $this->dispatchBrowserEvent(event: 'alert', data: [
            'type' => 'success',
            'message' => __('toast.comment.updated'),
        ]);
    }

    public function render(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $cart = $this->cart;
        $user = $cart->user;

        $locationsInvoice = $user->locations()->where('type', LocationTypeEnum::INVOICE->value)->get();
        $locationsShipping = $user->locations()->where('type', LocationTypeEnum::SHIPPING->value)->get();

        return view(view: 'livewire.location-cart', data: compact('cart', 'locationsInvoice', 'locationsShipping'));
    }
}
