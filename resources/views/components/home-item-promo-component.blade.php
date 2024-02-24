<section id="x-home-item-promo-component" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
    <div class="container">
        @if (count($itemsPromo) > 0)
            <h2 class="cetronic-title"> {{ __('home.items.promo') }}</h2>
            <div id="owl-example" class="owl-carousel owl-items">
                @foreach ($itemsPromo as $itemPromo)
                    <x-items.item-component :item="$itemPromo" />
                @endforeach
            </div>
        @endif
    </div>
</section>
