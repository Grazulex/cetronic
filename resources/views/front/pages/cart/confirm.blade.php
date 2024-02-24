@extends('front.layout.default')
@section('content')
    <section class="contact-us">
        <div class="container">
            <div class="row">
                <h2 class="contact-us_title">{{ __('cart.title') }}</h2>
            </div>
        </div>
    </section>
    <section>
        <div class="container">

            <div class="row">
                <div class="col-xl-9">
                    <div class="table-responsive items-panier">
                        <table class="table panier">
                            <thead>
                            <tr>
                                <th class="text-center">{{ __('cart.header.item') }}</th>
                                <th scope="col">{{ __('cart.header.qty') }}</th>
                                <th scope="col">{{ __('cart.header.price') }}</th>
                                <th scope="col">{{ __('cart.header.total') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($cartLines as $cartLine)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="img-panier">
                                                @if ($cartLine->item->master)
                                                    <x-items.picture2-component :item="$cartLine->item->master" />
                                                @else
                                                    <x-items.picture2-component :item="$cartLine->item" />
                                                @endif
                                            </div>
                                            <!--<div class="img-panier"></div>-->
                                            <span class="ps-4">{{ $cartLine->item->brand->name }}
                                                {{ $cartLine->item->reference }} {{ $cartLine->variant }}</span>
                                        </div>
                                    </td>
                                    <td class="w-25">
                                        {{ $cartLine->quantity }}
                                    </td>
                                    @livewire('price-cart', ['cartItem' => $cartLine])
                                    @livewire('price-total-cart', ['cartItem' => $cartLine])
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-xl-3">
                    @livewire('total-cart', ['cart' => $cart])

                    <h4 class="text-center">{{ __('cart.delivery') }}</h4>
                    {{ $cart->shippingLocation->company }}
                    {{ $cart->shippingLocation->firstname }}
                    {{ $cart->shippingLocation->lastname }}
                    <br>
                    {{ $cart->shippingLocation->street }}, {{ $cart->shippingLocation->street_number }}
                    <br>
                    {{ $cart->shippingLocation->zip_code }}
                    {{ $cart->shippingLocation->city }}
                    <br>
                    {{ $cart->shippingLocation->country->value }}
                    <br>
                    {{ $cart->shippingLocation->vat }}


                    <h4 class="text-center">{{ __('cart.invoice') }}</h4>
                    {{ $cart->invoiceLocation->company }}
                    {{ $cart->invoiceLocation->firstname }}
                    {{ $cart->invoiceLocation->lastname }}
                    <br>
                    {{ $cart->invoiceLocation->street }}, {{ $cart->invoiceLocation->street_number }}
                    <br>
                    {{ $cart->invoiceLocation->zip_code }}
                    {{ $cart->invoiceLocation->city }}
                    <br>
                    {{ $cart->invoiceLocation->country->value }}
                    <br>
                    {{ $cart->invoiceLocation->vat }}
                    <br>
                    <x-cart.button-next-component :cart="$cart" type="confirm" />
                </div>
            </div>

        </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
