@extends('front.layout.default')
@section('content')
    @php
        $milestones = ['m1', 'm2', 'm3', 'm4', 'm5'];
        $team = [
            'geoffrey' => 'Geoffrey Bhamani',
            'stephanie' => 'Stéphanie Currenti',
            'stieven' => 'Stieven Stroobants',
            'manal' => 'Manal Tabakkalt',
            'jeanmarie' => 'Jean-Marie Grosjean',
        ];
        $representatives = [
            'brussels' => 'Arnaud Blanche',
            'flanders' => 'Ludo Broeckx',
            'france' => null,
            'domtom' => null,
        ];
        $initials = fn (string $name): string => collect(preg_split('/[\s-]+/u', $name))
            ->map(fn (string $part): string => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');
    @endphp

    <div class="about">
        <header class="about-intro">
            <h1 class="about-title">{{ __('about.intro.title') }}</h1>
            <p class="about-lead">{{ __('about.intro.lead') }}</p>
        </header>

        <section class="about-section about-story" aria-labelledby="about-story-title">
            <div class="about-story_aside">
                <h2 id="about-story-title" class="about-heading">{{ __('about.story.title') }}</h2>
            </div>
            <ol class="about-timeline">
                @foreach ($milestones as $milestone)
                    <li class="about-timeline_item">
                        <span class="about-timeline_year">{{ __("about.story.{$milestone}.year") }}</span>
                        <h3 class="about-timeline_title">{{ __("about.story.{$milestone}.title") }}</h3>
                        <p class="about-text">{{ __("about.story.{$milestone}.text") }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="about-values" aria-labelledby="about-values-title">
            <div class="about-values_inner">
                <div>
                    <h2 id="about-values-title" class="about-heading">{{ __('about.values.title') }}</h2>
                    <p class="about-text">{{ __('about.values.intro') }}</p>
                </div>
                <ul class="about-values_list">
                    <li>{{ __('about.values.v1') }}</li>
                    <li>{{ __('about.values.v2') }}</li>
                    <li>{{ __('about.values.v3') }}</li>
                </ul>
            </div>
        </section>

        <section class="about-section" aria-labelledby="about-team-title">
            <h2 id="about-team-title" class="about-heading about-heading--center">{{ __('about.team.title') }}</h2>
            <ul class="about-team">
                @foreach ($team as $key => $name)
                    <li class="about-person">
                        <span class="about-person_monogram" aria-hidden="true">{{ $initials($name) }}</span>
                        <div>
                            <h3 class="about-person_name">{{ $name }}</h3>
                            <p class="about-person_role">{{ __("about.team.{$key}.role") }}</p>
                            <p class="about-text">{{ __("about.team.{$key}.text") }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="about-section about-reps" aria-labelledby="about-reps-title">
            <div class="about-reps_head">
                <h2 id="about-reps-title" class="about-heading">{{ __('about.reps.title') }}</h2>
                <p class="about-text">{{ __('about.reps.intro') }}</p>
            </div>
            <ul class="about-regions">
                @foreach ($representatives as $key => $name)
                    <li class="about-region">
                        <h3 class="about-region_name">{{ __("about.reps.{$key}.region") }}</h3>
                        @if ($name)
                            <p class="about-region_person">{{ $name }}</p>
                        @endif
                        <p class="about-text">{{ __("about.reps.{$key}.text") }}</p>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="about-contact" aria-labelledby="about-contact-title">
            <div class="about-contact_text">
                <h2 id="about-contact-title" class="about-heading">{{ __('about.contact.title') }}</h2>
                <p class="about-text">{{ __('about.contact.text') }}</p>
            </div>
            <div class="about-contact_actions">
                <a class="about-contact_link" href="tel:+3224241820">+32 (0)2 424 18 20</a>
                <a class="about-contact_link" href="mailto:info@cetronicbenelux.com">info@cetronicbenelux.com</a>
                <a class="about-contact_button" href="{{ route('contact') }}">{{ __('about.contact.cta') }}</a>
            </div>
        </section>
    </div>

    <x-section-brands-component />
    <x-section-features-component />
@stop
