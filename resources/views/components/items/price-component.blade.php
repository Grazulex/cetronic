<div>
    @if ($price['price_start'] != $price['price_end'])
        <font class="strike">@money($price['price_start'])</font>
    @endif
    {{-- The promo price is struck only when another price (customer discount, fixed price) replaces it. --}}
    @if ($price['price_promo'] > 0 && $price['price_promo'] != $price['price_end'])
        <font class="strike">@money($price['price_promo'])</font>
    @endif

    @if ($price['price_end'] > 0)
        @money($price['price_end'])
    @else
        {{ __('item.price.login') }}
    @endif

    @if ($price['sale'])
        <font class="sale">{{ __('item.price.sale') }} @money($price['sale'])</font>
    @endif
</div>
