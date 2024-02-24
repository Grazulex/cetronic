<div class="container">

    <h3 class="cart-location-title mb-2">{{ __('cart.comment.title') }}</h3>
    <textarea class="form-control" name="comment" wire:model.lazy="comment" rows="3"></textarea>

    <h3 class="cart-location-title mb-2">{{ __('cart.location.title') }}</h3>

    <div>
        {{ __('cart.location.description') }}
    </div>


    <div class="form-group mt-2 ">

        <label class="champ-form-commande" for="">{{ __('cart.location.invoice.name') }}</label>
        @if (count($locationsInvoice) > 0)
            @foreach ($locationsInvoice as $locationInvoice)
                <input class="form-radio" type="radio" name="locationInvoice" wire:model="locationInvoice"
                       value="{{ $locationInvoice->id }}" /> {{ $locationInvoice->full_name }}
                <a class="icon-action px-2"
                   href="{{ route('user_location.edit', ['location' => $locationInvoice->id]) }}">
                    <svg width="27" height="26" viewBox="0 0 27 26" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M23.141 14.3723V22.8745C23.141 24.0435 22.1447 25 20.9269 25H3.2141C1.99635 25 1 24.0435 1 22.8745V5.87011C1 4.70106 1.99635 3.74456 3.2141 3.74456H11.6498M10.9635 15.4351H15.3917L25.6762 5.56195C26.1079 5.14747 26.1079 4.47788 25.6762 4.0634L22.8088 1.31086C22.3771 0.89638 21.6797 0.89638 21.248 1.31086L10.9635 11.184V15.4351ZM10.9635 15.4351L9.8564 16.4978M18.7128 3.74456L23.141 7.99565"
                            stroke="#4D4D4D" stroke-miterlimit="10" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </a>
                <br>
            @endforeach
        @else
            {{ __('cart.location.invoice.empty') }}
        @endif
    </div>

    <div class="form-group mt-2">
        <label class="champ-form-commande" for="">{{ __('cart.location.shipping.name') }}</label>
        @if (count($locationsShipping) > 0)
            @foreach ($locationsShipping as $locationShipping)
                <input class="form-radio" type="radio" name="locationShipping" wire:model="locationShipping"
                       value="{{ $locationShipping->id }}" /> {{ $locationShipping->full_name }}
                <a class="icon-action px-2"
                   href="{{ route('user_location.edit', ['location' => $locationShipping->id]) }}">
                    <svg width="27" height="26" viewBox="0 0 27 26" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M23.141 14.3723V22.8745C23.141 24.0435 22.1447 25 20.9269 25H3.2141C1.99635 25 1 24.0435 1 22.8745V5.87011C1 4.70106 1.99635 3.74456 3.2141 3.74456H11.6498M10.9635 15.4351H15.3917L25.6762 5.56195C26.1079 5.14747 26.1079 4.47788 25.6762 4.0634L22.8088 1.31086C22.3771 0.89638 21.6797 0.89638 21.248 1.31086L10.9635 11.184V15.4351ZM10.9635 15.4351L9.8564 16.4978M18.7128 3.74456L23.141 7.99565"
                            stroke="#4D4D4D" stroke-miterlimit="10" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </a>
                <br>

            @endforeach
        @else
            {{ __('cart.location.shipping.empty') }}
        @endif
    </div>

    <div class="text-center mt-5">
        <a class="login_btn" href="{{ route('user_locations.list') }}">{{ __('cart.location.shipping.create') }}</a>
    </div>

</div>
