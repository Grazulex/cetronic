@extends('front.layout.default')
@section('title'){{ trim($slug) }}@stop
@section('description')
    {{ trim($slug) }}
@stop
@section('content')
    <section class="section-listing">
        <h2 class="listing-title">
            @if ($type == 'brand')
                <div class="d-flex align-items-center justify-content-center">
                    <x-brands.picture-component :brand="$model" :key="'picture-' . $model->id" />
                </div>
            @else
                {{ $name }}
            @endif
        </h2>
        <div class="container">
            <div class="row">
                <div style="float:left;width:100%;">
                    @if ($description)
                        <div class="listing-description">
                            {!! $description !!}
                        </div>
                    @endif
                    @if ($type == 'brand' && Auth::check())
                        <div class="row">
                            <div class="col-6 d-flex flex-grow flex-column">
                                <a href="{{ route('brand.download.zip', [$model, $catSlug]) }}" class="login_btn single-word">{{ __('listing.brand_download_zip') }}</a>
                            </div>
                            <div class="col-6 d-flex flex-grow flex-column">
                                <a href="{{ route('brand.download.catalog', [$model, $catSlug]) }}" class="login_btn single-word">{{ __('listing.brand_download_catalog') }}</a>
                            </div>

                        </div>
                        <br/><br/>
                    @endif
                </div>

            </div>

            <x-items.list-component :type="$type" :slug="$slug" :catSlug="$catSlug" />

    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
