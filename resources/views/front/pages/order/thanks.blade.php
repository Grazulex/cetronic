@extends('front.layout.default')
@section('content')
    <section class="contact-us">
        <div class="container">
            <div class="row">
                <h2 class="contact-us_title">{!! __('order.title.thanks') !!}</h2>
            </div>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="row">
                <p class="contact_text">{!! __('order.thanks') !!}</p>
            </div>
        </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
