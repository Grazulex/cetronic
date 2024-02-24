@extends('front.layout.default')
@section('content')

    <section class="section_about-us" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
        <h2 class="section_about-us_title">{{ __('about.section2.title') }}</h2>
        <div class="container">
            <div class="section1_about-us">
                <div class="row d-flex align-items-center">
                    <div class="col-xl-6">
                        <div class="section1-contenu">
                            <h2 class="section1-contenu_title">{{ __('about.section2.subtitle') }}</h2>
                            <div class="section1-contenu_discription">
                                {!! __('about.section2.description') !!}

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="section1-bg-img"></div>
                    </div>
                </div>
            </div>


            <div class="section2_about-us" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
                <div class="row d-flex align-items-center">
                    <div class="col-xl-6">
                        <div class="section2-bg-img"></div>
                    </div>

                    <div class="col-xl-6">
                        <div class="section2-contenu">
                            <h2 class="section2-contenu_title">{{ __('about.section3.title1') }}</h2>
                            <div class="section2-contenu_discription">
                                {!! __('about.section3.description1') !!}
                            </div>

                            <h2 class="section2-contenu_title">{{ __('about.section3.title2') }}</h2>
                            <div class="section2-contenu_discription">
                                {!! __('about.section3.description2') !!}
                            </div>
                        </div>
                    </div>

                </div>
            </div>


        </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />

@stop
