<div class="cart-total">
    <div class="text-center total-a-payer">{{ __('cart.total') }}</div>
    <div class="details-total">
        <div class="row mt-3">
            <div class="col-xl-6 d-flex justify-content-start">
                <span class="cart-info">{{ __('cart.shipping.title') }}</span>
            </div>
            <div class="col-xl-6 d-flex justify-content-end">
                @auth
                    @if ($franco > 0)
                        @if ($total < $franco)
                            @if ($shippingPrice > 0)
                                @money($shippingPrice)<br>
                            @endif
                            {{ __('cart.shipping.franco') }}: {{ $franco }} €
                        @else
                            {{ __('cart.shipping.free') }}
                        @endif
                    @else
                        @if ($shippingPrice > 0)
                            @money($shippingPrice)
                        @else
                            {{ __('cart.shipping.order') }}
                        @endif
                    @endif
                @else
                    {{ __('cart.shipping.login') }}
                @endauth
            </div>
        </div>
    </div>
    <div class="details-total">
        <div class="row mt-3">
            <div class="col-xl-6 d-flex justify-content-start">
                <span class="cart-info">{{ __('cart.item.total') }}</span>
            </div>
            <div class="col-xl-6 d-flex justify-content-end">
                @money($total)
            </div>
        </div>
        @if ($discount > 0)
            <div class="row mt-3">
                <div class="col-xl-6 d-flex justify-content-start">
                    <span>{{ __('cart.discount') }}</span>
                </div>
                <div class="col-xl-6 d-flex justify-content-end">
                    -@money($discount)
                </div>
            </div>
        @endif
        @if ($hasVAT)
            <div class="row mt-3">
                <div class="col-xl-6 d-flex justify-content-start">
                    <span>{{ __('cart.vat') }}</span>
                </div>
                <div class="col-xl-6 d-flex justify-content-end">
                    @money($VAT+$shippingVAT)
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-xl-6 d-flex justify-content-start">
                    <span>{{ __('cart.total') }}</span>
                </div>
                <div class="col-xl-6 d-flex justify-content-end">
                    @money($totalVAT+$shippingTotalVAT)
                </div>
            </div>
            <div class="row mt-3 mb-3">
                <span>{{ __('cart.description') }}</span>
            </div>
        @endif
    </div>
</div>
