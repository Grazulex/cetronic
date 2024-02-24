<section class="section-marques" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
    <div class="container">
        <div class="row">
            <span class="cetronic-title">{{ __('home.brands.slide') }}</span>
        </div>

        <div class="row">
            <div class="nos-marques">
                <div id="owl-example" class="owl-carousel owl-nos-marques">
                    @foreach ($brands as $brand)
                        <div class="d-flex align-items-center justify-content-center">
                            <x-brands.picture-component :brand="$brand" :key="'picture-' . $brand->id" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
