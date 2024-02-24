@extends('front.layout.default')
@section('content')
    <section class="cart_page">
        <h2 class="cart_page_title">{{ __('cart.title') }}</h2>

        <div class="container">
            @if (isset($cartLines) && count($cartLines) > 0)

                <div class="row">
                    <div class="col-xl-9">
                        <div class="table-responsive items-panier">

                            <table class="table panier ">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{ __('cart.header.item') }}</th>
                                        <th scope="col">{{ __('cart.header.qty') }}</th>
                                        <th scope="col">{{ __('cart.header.price') }}</th>
                                        <th scope="col">{{ __('cart.header.total') }}</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cartLines as $cartLine)
                                        <tr id="{{ $cartLine->id }}" data-id="{{ $cartLine->id }}">
                                            <td>
                                                <div class="d-flex align-items-center">

                                                    <div class="img-panier">
                                                        @if ($cartLine->item->master)
                                                            <x-items.picture2-component :item="$cartLine->item->master" />
                                                        @else
                                                            <x-items.picture2-component :item="$cartLine->item" />
                                                        @endif
                                                    </div>
                                                    <span class="ps-4">{{ $cartLine->item->brand->name }}
                                                        {{ $cartLine->item->reference }}</span>
                                                </div>
                                            </td>
                                            <td>

                                                @livewire('form-cart', ['cartItem' => $cartLine])

                                            </td>
                                            @livewire('price-cart', ['cartItem' => $cartLine])
                                            @livewire('price-total-cart', ['cartItem' => $cartLine])
                                            @livewire('delete-cart', ['cartItem' => $cartLine])
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>


                        </div>
                    </div>
                    <div class="col-xl-3">
                        @livewire('total-cart', ['cart' => $openCart])
                        <x-cart.button-next-component :cart="$openCart" type="items" />
                    </div>
                </div>
            @else
                {{ __('cart.empty') }}
            @endif
        </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
