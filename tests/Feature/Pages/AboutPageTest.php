<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;

it('presents the company story, team, representatives and contact', function (string $locale): void {
    App::setLocale($locale);

    $html = view('front.pages.about')->render();

    expect($html)
        ->toContain(e(__('about.intro.title')))
        ->toContain(e(__('about.story.m1.title')))
        ->toContain(e(__('about.story.m4.text')))
        ->toContain(e(__('about.values.v2')))
        ->toContain(e(__('about.team.manal.text')))
        ->toContain(e(__('about.reps.flanders.region')))
        ->toContain('Geoffrey Bhamani')
        ->toContain('Jean-Marie Grosjean')
        ->toContain('Arnaud Blanche')
        ->toContain('Ludo Broeckx')
        ->toContain('href="tel:+3224241820"')
        ->toContain('href="mailto:info@cetronicbenelux.com"')
        ->toContain('href="' . route('contact') . '"')
        ->not->toContain('about.')
        ->not->toContain('section1-bg-img')
        ->not->toContain('section2-bg-img');
})->with(['fr', 'nl', 'en']);

it('orders the story milestones chronologically', function (): void {
    App::setLocale('fr');

    $html = view('front.pages.about')->render();

    $positions = array_map(fn(string $year): int => mb_strpos($html, ">{$year}<"), ['1978', '1980', '1994', 'Aujourd’hui']);

    expect($positions)->each->toBeInt()
        ->and($positions)->toBe(collect($positions)->sort()->values()->all());
});

it('serves the about page', function (): void {
    $this->get(route('about'))->assertOk();
});
