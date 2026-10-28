<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

it('links the home slides to the watches and bracelets categories', function (?string $locale): void {
    if ($locale) {
        $this->refreshApplicationWithLocale($locale);
    }

    $this->get(uri: route('home'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee(route('list', ['cat' => 'watches', 'type' => 'category', 'slug' => 'watches']), false)
        ->assertSee(route('list', ['cat' => 'bracelets', 'type' => 'category', 'slug' => 'bracelets']), false);
})->with(['en' => null, 'fr' => 'fr', 'nl' => 'nl']);

it('links the home about button to the about page', function (): void {
    $this->get(uri: route('home'))
        ->assertSee('href="' . route('about') . '" class="cetronic-about-btn"', false);
});

it('asks to choose a country instead of preselecting the first one on register', function (): void {
    $this->get(uri: route('register'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('<option value="" disabled selected>' . __('user.choose_country') . '</option>', false);
});
