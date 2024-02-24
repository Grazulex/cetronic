@extends('front.layout.default')
@section('content')
    <section class="contact-us">
        <div class="container">
            <div class="row">
                <h2 class="contact-us_title">{{ __('contact.help.title') }}</h2>
            </div>
        </div>
    </section>
    <section>
        <form method="POST" action="{{ route('help.send') }}" id="contactUSForm">
        {{ csrf_field() }}
        <div class="container">
            <div class="row">
                <p class="contact_text">{{ __('contact.help.description') }}</p>
            </div>
            <div class="row">
                <div class="col-xl-7">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-xl-6"><input type="email" name="email" class="form-control contact-form" placeholder="{{ __('contact.help.form.email') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <textarea name="message" id="message" cols="30" rows="10" class="form-control contact-form"
                                placeholder="{{ __('contact.help.form.message') }}"></textarea>
                        </div>
                    </div>
                    <input type="text" name="title" style="visibility: hidden;">
                    <div class="row">
                        <div class="d-flex justify-content-center">
                            <input type="submit" value="{{ __('contact.help.form.submit') }}" name="submit" class="contact-us_btn"></input>
                        </div>
                    </div>

                </div>
                <div class="col-xl-5">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="section-contact">
                                <h2 class="contact_title">{{ __('contact.help.title') }}</h2>
                                <p>
                                    {{ __('contact.help.contact') }}
                                </p>
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
