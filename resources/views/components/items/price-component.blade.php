<div>
    @if ($price['price_start'] != $price['price_end'])
        <font class="strike">@money($price['price_start'])</font>
    @endif
    @if ($price['price_promo'] > 0)
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
