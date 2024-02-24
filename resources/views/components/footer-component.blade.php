<section data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
    <footer>
        <div class="container">
            <div class="row">

                <div class="col-xl-3">
                    <div class="footer-content">
                        <div class="footer-title">{{ __('home.footer.column1.title') }}</div>
                        <p><a class="text-decoration-none" href="{{ route('user_dashboard') }}">{{ ucfirst(__('home.footer.column1.order')) }}</a> </p>
                        <p><a class="text-decoration-none" href="{{ url('/contact') }}">{{ ucfirst(__('home.footer.column1.contact')) }}</a></p>
                        <p><a class="text-decoration-none" href="{{ url('/help') }}">{{ ucfirst(__('home.footer.column1.help')) }}</a></p>
                        <!--<p><a class="text-decoration-none" href="#">{{ ucfirst(__('home.footer.column1.catalog')) }}</a></p>-->
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="footer-content">
                        <div class="footer-title">{{ __('home.footer.column2.title') }}</div>
                        <!--<p><a class="text-decoration-none" href="#">{{ ucfirst(__('home.footer.column2.shop')) }}</a></p>-->
                        <p><a class="text-decoration-none" href="{{ url('/about') }}">{{ ucfirst(__('home.footer.column2.about')) }}</a></p>
                        <!--<p><a class="text-decoration-none"href="#">{{ ucfirst(__('home.footer.column2.blog')) }}</a></p>-->
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="footer-content">
                        <div class="footer-title">{{ __('home.footer.column3.title') }}</div>
                        <p><a class="text-decoration-none" href="#">{{ ucfirst(__('home.footer.column3.confidentality')) }}</a></p>
                        <p><a class="text-decoration-none" href="#">{{ ucfirst(__('home.footer.column3.conditions')) }}</a></p>
                        <p><a class="text-decoration-none" href="#">{{ ucfirst(__('home.footer.column3.cgv')) }}</a></p>
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="footer-content">
                        <div class="footer-title">{{ __('home.footer.column4.title') }}</div>
                        <p><a class="text-decoration-none" href="tel:{{ __('home.footer.column4.phone') }}">{{ __('home.footer.column4.phone') }}</a></p>
                        <p><a class="text-decoration-none"
                                href="mailto:{{ __('home.footer.column4.email') }}">{{ __('home.footer.column4.email') }}</a></p>
                        <!--<p>
                            <x-social-media-component />
                        </p>-->
                    </div>
                </div>
            </div>
        </div>
    </footer>
</section>
