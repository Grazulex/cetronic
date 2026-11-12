@extends('front.layout.default')
@section('content')
    @php
        $pages = [
            'privacy' => 'legal.privacy',
            'terms' => 'legal.terms',
            'after_sales' => 'legal.after_sales',
        ];
    @endphp

    <div class="legal">
        <header class="legal-intro">
            <h1 class="legal-title">{{ __("legal.{$page}.title") }}</h1>
            <p class="legal-lead">{{ __("legal.{$page}.intro") }}</p>
            <p class="legal-updated">{{ __('legal.updated') }}</p>
        </header>

        <nav class="legal-nav" aria-label="{{ __('home.footer.column3.title') }}">
            @foreach ($pages as $key => $route)
                <a href="{{ route($route) }}" @class(['legal-nav_link', 'is-active' => $key === $page])
                    @if ($key === $page) aria-current="page" @endif>{{ __("legal.{$key}.title") }}</a>
            @endforeach
        </nav>

        <div class="legal-body">
            @foreach (__("legal.{$page}.sections") as $section)
                <section class="legal-section">
                    <h2 class="legal-section_title">{{ $section['title'] }}</h2>
                    <div class="legal-section_text">{!! $section['text'] !!}</div>
                </section>
            @endforeach
        </div>
    </div>
@stop
