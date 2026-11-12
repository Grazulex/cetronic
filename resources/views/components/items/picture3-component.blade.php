<div class="picture3-component">

    @php
        $carouselId = ($itemCarouselId != null ? $itemCarouselId : 'productCarousel');
        $picturesCount = count($pictures);
        $picture = null;
    @endphp

    <div id="{{ $carouselId }}" class="carousel slide product-carousel" data-bs-interval="false">
        <!-- carousel img list -->
        <div class="carousel-inner">
            @foreach ($pictures as $picture)
            <div class="carousel-item {{ $loop->index == 0 ? 'active' : '' }}" data-count="{{ $picturesCount }}">
                @if ($slug != '')
                    <a href="{{ route('item', $slug) }}">
                @endif
                        @if (Route::is('item') )
                <div class="easyzoom easyzoom--overlay">
                    <a href="{{ $picture->getUrl() }}">
                        <div class="cetronic-card-img" style="background-image: url('{{ $picture->getAvailableUrl(['medium']) }}')">
                        </div>
                    </a>
                </div>
                        @else
                            <div class="cetronic-card-img" style="background-image: url('{{ $picture->getAvailableUrl(['medium']) }}')"></div>
                        @endif
                @if ($slug != '')
                    </a>
                @endif
            </div>
            @endforeach
        </div>

        <div class="clearfix"></div>


        <!-- carousel thumbnail -->
        @if ($showThumb==true)
            <div class="carousel-indicators mt-1">
                @if ($picturesCount > 1)
                    @foreach ($pictures as $picture)
                        <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->index == 0 ? 'active' : ' ' }}" aria-current="{{ $loop->index == 0 ? 'true' : 'false' }}" aria-label="Slide {{ $loop->iteration }}">
                            <!-- <div class="img-container"> -->
                            <img src="{{ $picture->getAvailableUrl(['thumb']) }}" class="img-fluid" alt="" loading="lazy" />
                            <!-- </div> -->
                        </button>
                    @endforeach

                @endif
            </div>
        @endif

        @if ($picturesCount > 1)
            <!-- carousel nav -->
            <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        @endif

    </div>

    @php
        // A promotion takes precedence over "new": both never show together.
        $showNew = $isNew && ! $promoType;
        $ribbonCount = (int) $showNew + (int) $isBestSeller + (int) (bool) $promoType;
    @endphp
    @if ($ribbonCount > 0)
        {{-- One band under the pictures (not over them, so thumbnails stay visible), split between the labels.
             fixed: price_fix for everyone; personal: promo price with the customer's own discount. --}}
        <div class="item-ribbons item-ribbons--{{ $ribbonCount }}">
            @if ($showNew)
                <span class="item-ribbon item-ribbon--new">{{ __('item.badge.new') }}</span>
            @endif
            @if ($isBestSeller)
                <span class="item-ribbon item-ribbon--best-seller">{{ __('item.badge.best_seller') }}</span>
            @endif
            @if ($promoType)
                <span @class(['item-ribbon', 'item-ribbon--' . $promoType])>{{ __('item.badge.promo_' . $promoType) }}</span>
            @endif
        </div>
    @endif
</div>
