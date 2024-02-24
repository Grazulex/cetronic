@extends('front.layout.default')
@section('content')

    <section class="login_page">
        <h2 class="login_page_title">{{ __('cart.title') }}</h2>
        <div class="container">

            <div class="row">
                <div class="col-xl-9">
                    @livewire('location-cart', ['cart' => $cart])
                </div>

                <div class="col-xl-3">
                    @livewire('total-cart', ['cart' => $cart])
                    @if ($cart->invoice_location_id && $cart->shipping_location_id)
                        <x-cart.button-next-component :cart="$cart" type="locations" />
                    @else
                        {{ __('cart.location.missing') }}
                    @endif
                </div>
            </div>

        </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
