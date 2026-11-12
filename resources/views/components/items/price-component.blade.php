@php
    $strikeStart = $price['price_start'] != $price['price_end'];
    $strikePromo = $price['price_promo'] > 0 && $price['price_promo'] != $price['price_end'];
    // The final price stands out whenever an older price is struck through; on promotions it takes the ribbon colour.
    $isDeal = $strikeStart || $strikePromo;
@endphp
<div class="price-component">
    @if ($strikeStart)
        <font class="strike">@money($price['price_start'])</font>
    @endif
    {{-- The promo price is struck only when another price (customer discount, fixed price) replaces it. --}}
    @if ($strikePromo)
        <font class="strike">@money($price['price_promo'])</font>
    @endif

    @if ($price['price_end'] > 0)
        <span @class(['price-final', 'price-final--deal' => $isDeal, 'price-final--' . $promoType => $isDeal && $promoType])>@money($price['price_end'])</span>
    @else
        {{ __('item.price.login') }}
    @endif

    @if ($price['sale'])
        <font class="sale">{{ __('item.price.sale') }} @money($price['sale'])</font>
    @endif
</div>
