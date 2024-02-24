<div class="container" data-aos="fade-right" data-aos-delay="200" data-aos-duration="1000">
    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators slide-indicators">
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class=" active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" class="" aria-label="Slide 2"></button>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item cetronic-slide-bg-img active" style="background: url('https://i.goopics.net/63v9u2.png');">
                <div class="Slide-Contenu">
                    <p class="Slide-description">{{ __('home.slide1.description') }}</p>
                    <p class="Slide-title">{{ __('home.slide1.title') }}</p>
                    <a href="{{ route('list', ['cat' => __('home.slide1.route'), 'type' => 'category', 'slug' => __('home.slide1.route')]) }}"><button class="slide-btn">{{ __('home.slide1.action') }}</button></a>
                </div>
                <div class="scrolldown">
                    <a href="#x-home-item-promo-component" aria-label="link">
                        <span></span><span></span><span></span>
                    </a>
                </div>
            </div>
            <div class="carousel-item cetronic-slide-bg-img" style="background: url('https://i.goopics.net/n9fe9j.png');">
                <div class="Slide-Contenu">
                    <p class="Slide-description">{{ __('home.slide2.description') }}</p>
                    <p class="Slide-title">{{ __('home.slide2.title') }}</p>
                    <a href="{{ route('list', ['cat' => __('home.slide2.route'), 'type' => 'category', 'slug' => __('home.slide2.route')]) }}"><button class="slide-btn">{{ __('home.slide2.action') }}</button></a>
                </div>
                <div class="scrolldown">
                    <a href="#x-home-item-promo-component" aria-label="link">
                        <span></span><span></span><span></span>
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>