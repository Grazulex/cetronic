<div class="picture3-component">
    @if ($isNew)
        <div class="if-new">
            <p>{{ __('item.badge.new') }}</p>
        </div>
    @endif

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
                        <div class="cetronic-card-img" style="background-image: url('{{ $picture->getUrl() }}')">
                        </div>
                    </a>
                </div>
                        @else
                            <div class="cetronic-card-img" style="background-image: url('{{ $picture->getUrl() }}')"></div>
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
                            <img src="{{ $picture->getUrl() }}" class="img-fluid" alt="" />
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
</div>
