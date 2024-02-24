<div>
        <div class="row d-flex align-items-center mt-2">
                <!-- pe-1 -->
                <div class="col-12 col-xl-6 pe-auto pe-xl-1">
                <div class="step-down-upp">
                    <span wire:click="down({{ $item->id }})"
                        class="border-0 bg-body">-</span>
                    <input class="text-center border-0 w-100" wire:model="quantity.{{ $item->id }}" type="number"
                        step="{{ $item->multiple_quantity }}" min="{{ $item->multiple_quantity }}">
                    <span wire:click="up({{ $item->id }})
                        class="border-0 bg-body plus">+</span>
                </div>
            </div>
            <!-- ps-1 -->
            <div class="col-12 col-xl-6 pt-1 pt-xl-0 ps-xl-1">
                <button wire:click="addToCart({{ $item->id }})" class="btn cetronic-card-button d-flex align-items-center justify-content-center w-100" type="button">
                    <svg width="20" height="24" viewBox="0 0 20 27" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M5.64917 9.69565V5.8913C5.64917 3.18478 7.65917 1 10.1492 1C12.6392 1 14.6492 3.18478 14.6492 5.8913V9.69565M16.2992 26H3.99919C2.20919 26 0.819175 24.3152 1.01917 22.3804L2.64918 6.43478H17.6492L19.2792 22.3804C19.4792 24.3152 18.0892 26 16.2992 26Z"
                            stroke="#ffffff" stroke-width="0.8" stroke-miterlimit="10" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <!-- ps-2 -->
                    <span class="add-to-panier ps-xl-2">{{ ucfirst(__('item.form.add')) }}</span>
                </button>
            </div>
        </div>
</div>
