@extends('front.layout.default')

@section('title')
    {{ __('title.homepage') }}
@stop
@section('description')
    {{ __('description.homepage') }}
@stop
@section('content')
    <x-slide-component />
    <x-section-message />
    @if (auth()->check())
        <x-home-item-promo-component qty="8" />
    @endif
    <x-section-categories-component />
    <x-home-item-new-component qty="8" />
    <x-section-about-component />
    <x-section-brands-component />
    <x-section-features-component />

    {{-- <x-home-brand-featured-component/> --}}
@stop
