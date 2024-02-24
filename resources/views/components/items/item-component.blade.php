<div class="card">
    @if ($item->master || $item->variants->count() > 0)
        @if ($item->master)
            <x-items.picture3-component :item="$item->master" slug="{{ $item->master->slug }}"
                                        :itemCarouselId="'carousel-main-'.$item->id" />
        @else
            <x-items.picture3-component :item="$item" slug="{{ $item['slug'] }}"
                                        :itemCarouselId="'carousel-main-'.$item->id" />
        @endif
    @else
        <x-items.picture3-component :item="$item" slug="{{ $item['slug'] }}"
                                    :itemCarouselId="'carousel-main-'.$item->id" />
    @endif
    <div class="card-body">
        <h5 class="item-reference">
            @if ($item->master || $item->variants->count() > 0)
                @if ($item->master)
                    <a href="{{ route('item', $item->master->slug) }}" class="show_hover">
                        @else
                            <a href="{{ route('item', $item['slug']) }}" class="show_hover">
                                @endif
                                @else
                                    <a href="{{ route('item', $item['slug']) }}" class="show_hover">
                                        @endif
                                        {{ $item->reference }}
                                        @if (!$item->master)
                                            @php
                                                $itemService = new \App\Services\ItemService($item);
                                                echo $itemService->getVariantIfChoice();
                                            @endphp
                                        @endif
                                    </a>
        </h5>
        <x-items.price-component :item="$item" />
        @if ($item->master || $item->variants->count() > 0)

            @if ($item->master)
                <a href="{{ route('item', $item->master->slug) }}"
                   class="btn cetronic-card-button d-flex align-items-center justify-content-center mt-2"
                   type="submit">
                    @else
                        <a href="{{ route('item', $item['slug']) }}"
                           class="btn cetronic-card-button d-flex align-items-center justify-content-center mt-2"
                           type="submit">
                            @endif

                            <svg width="20" height="24" viewBox="0 0 20 27" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M5.64917 9.69565V5.8913C5.64917 3.18478 7.65917 1 10.1492 1C12.6392 1 14.6492 3.18478 14.6492 5.8913V9.69565M16.2992 26H3.99919C2.20919 26 0.819175 24.3152 1.01917 22.3804L2.64918 6.43478H17.6492L19.2792 22.3804C19.4792 24.3152 18.0892 26 16.2992 26Z"
                                    stroke="#ffffff" stroke-width="0.8" stroke-miterlimit="10" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <span class="add-to-panier ps-2">{{ __('item.view') }}</span>
                        </a>
            @else
                @livewire('form-item', ['item' => $item])
            @endif
    </div>
</div>
