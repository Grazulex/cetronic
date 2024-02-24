@extends('front.layout.default')
@section('content')
    <section class="contact-us">
        <div class="container">
            <div class="row">
                <h2 class="contact-us_title">{{ __('contact.title') }}</h2>
            </div>
        </div>
    </section>
    <section>
        <form method="POST" action="{{ route('contact.send') }}" id="contactUSForm">
        {{ csrf_field() }}
        <div class="container">
            <div class="row">
                <p class="contact_text">{{ __('contact.description') }}</p>
            </div>
            <div class="row">
                <div class="col-xl-7">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-xl-6">
                            <input type="text" required name="lastname" class="form-control contact-form" placeholder="{{ __('contact.form.lastname') }}">
                        </div>
                        <div class="col-xl-6">
                            <input type="text" required name="firstname" class="form-control contact-form" placeholder="{{ __('contact.form.firstname') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6"><input type="email" name="email" class="form-control contact-form" placeholder="{{ __('contact.form.email') }}">
                        </div>
                        <div class="col-xl-6"><input type="tel" name="phone" class="form-control contact-form" placeholder="{{ __('contact.form.phone') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <textarea name="message" id="message" cols="30" rows="10" class="form-control contact-form"
                                placeholder="{{ __('contact.form.message') }}"></textarea>
                        </div>
                    </div>
                    <input type="text" name="title" style="visibility: hidden;">
                    <div class="row">
                        <div class="d-flex justify-content-center">
                            <input type="submit" value="{{ __('contact.form.submit') }}" name="submit" class="contact-us_btn"></input>
                        </div>
                    </div>

                </div>
                <div class="col-xl-5">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="section-contact">
                                <h2 class="contact_title">{{ __('contact.title') }}</h2>
                                <p>
                                    {{ __('contact.contact') }}
                                </p>
                                <ul class="contact_info">
                                    <li><span>{{ __('contact.form.email') }} : </span><a class="text-decoration-none"
                                href="mailto:{{ __('home.footer.column4.email') }}">{{ __('home.footer.column4.email') }}</a></li>
                                    <li><span>{{ __('contact.form.phone') }} : </span><a class="text-decoration-none" href="tel:{{ __('home.footer.column4.phone') }}">{{ __('home.footer.column4.phone') }}</a></li>
                                    <li><span>{{ __('contact.form.address') }} : </span><a class="text-decoration-none" href="https://www.google.com/maps?ll=50.878089,4.338921&z=16&t=m&hl=fr&gl=FR&mapclient=embed&cid=5607738626403407129" target="_blank">{{ __('home.footer.column4.address') }}</a></li>
                                    <li>
                                        <div class="contact_map">
                                            <iframe class="map"
                                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2517.465807559617!2d4.336732116077385!3d50.87808857953664!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47c3c3b87220d5a7%3A0x4dd2b091d4031519!2sCetronic%20Benelux!5e0!3m2!1sfr!2sfr!4v1680423589027!5m2!1sfr!2sfr"
                                                width="600"
                                                height="450"
                                                style="border:0;"
                                                allowfullscreen=""
                                                loading="lazy"
                                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                                        </div>
                                    </li>

                                    <!--<li><span>WhatsApp : </span><a href="tel:+212 669 34 34 34">+212 669 34 34 34</a></li>-->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                        </form>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
