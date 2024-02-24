<div>
    <form wire:submit.prevent="updateCart({{ $cartItem->id }})" action="#">
        <input type="hidden" value="">
        <div class="d-flex align-items-center update-panier">
            <div class="row">
                <div class="col-9 d-flex justify-content-start">
                    <input wire:model="quantity.{{ $cartItem->id }}" class="form-control btn-panier text-center "
                        type="number" step="{{ $cartItem->item->multiple_quantity }}"
                        min="{{ $cartItem->item->multiple_quantity }}">
                </div>
                <div class="col-3 d-flex justify-content-end align-items-center">
                    <button class="update-icon" type="submit">
                        <svg width="20" height="20" viewBox="0 0 25 25" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M1 12.5C1 6.15 6.15 1 12.5 1C17.18 1 21.21 3.8 23 7.81M23 3V8H18M2 17.19C3.79 21.2 7.82 24 12.5 24C18.85 24 24 18.85 24 12.5M2 22V17H7"
                                stroke="#4D4D4D" stroke-miterlimit="10" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
