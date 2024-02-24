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
                    <div class="cetronic-card-img" style="background-image: url('{{ $picture->getUrl() }}')"></div>
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

        @if (Route::is('item') )
        <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#zoomModal_{{ $item->id }}">
            <svg width='16px' height='16px'>
                //zoom svg
                <path d='M15.5,14.5l-4.5-4.5c0.8-1.1,1.3-2.5,1.3-4c0-3.9-3.1-7-7-7S-2,2.1-2,6s3.1,7,7,7c1.5,0,2.9-0.5,4-1.3l4.5,4.5c0.2,0.2,0.5,0.2,0.7,0l1-1C15.7,15,15.7,14.7,15.5,14.5z M6,10c-2.8,0-5-2.2-5-5s2.2-5,5-5s5,2.2,5,5S8.8,10,6,10z' />
            </svg>
        </button>
        <div class="modal fade" id="zoomModal_{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        @if ($picture)
                            <img src='{{ $picture->getUrl() }}' class="img-fluid" alt="" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
</div>
