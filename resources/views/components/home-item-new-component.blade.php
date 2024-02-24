<div class="container" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
    @if (count($itemsNew) > 0)
        <h2 class="cetronic-title">{{ __('home.items.new') }}</h2>
        <div id="owl-example" class="owl-carousel owl-items">
            @foreach ($itemsNew as $itemNew)
                <x-items.item-component :item="$itemNew" />
            @endforeach
        </div>
    @endif
</div>
