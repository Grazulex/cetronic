<div class="d-flex justify-content-center">
    @auth
        @if ($type == 'locations')
            <a href="{{ route('cart.confirm', ['cart' => $cart]) }}" class="login_btn">
                {{ __('cart.confirm') }}
            </a>
        @elseif ($type == 'items')
            <a href="{{ route('cart.locations', ['cart' => $cart]) }}" class="login_btn">
                {{ __('cart.locations') }}
            </a>
        @elseif ($type == 'confirm')
            <a href="{{ route('cart.store', ['cart' => $cart]) }}" class="login_btn" onClick="this.disabled=true;">
                {{ __('cart.save') }}
            </a>
        @endif
    @else
        {{ __('cart.login.next') }}
    @endauth
</div>
