<td>
    @if ($price_start != $price_end)
        <font class="strike">@money($price_start)</font>
    @endif
    @if ($price_promo > 0)
        <font class="strike">@money($price_promo)</font>
    @endif

    @if ($price_end > 0)
        @money($price_end)
    @else
        {{ __('item.price.login') }}
    @endif
</td>
