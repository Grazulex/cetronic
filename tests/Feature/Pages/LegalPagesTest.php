<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;

it('serves each legal page with its sections', function (string $route, string $page): void {
    $response = $this->get(route($route))->assertOk();

    $response->assertSee(__("legal.{$page}.title"))
        ->assertSee(__('legal.updated'))
        ->assertDontSee("legal.{$page}.");

    foreach (__("legal.{$page}.sections") as $section) {
        $response->assertSee($section['title']);
    }
})->with([
    'privacy' => ['legal.privacy', 'privacy'],
    'terms' => ['legal.terms', 'terms'],
    'after-sales' => ['legal.after_sales', 'after_sales'],
]);

it('has the same sections in every language', function (string $page): void {
    $counts = array_map(fn(string $locale): int => count(trans("legal.{$page}.sections", [], $locale)), ['fr', 'nl', 'en']);

    expect(array_unique($counts))->toHaveCount(1)
        ->and($counts[0])->toBeGreaterThan(3);
})->with(['privacy', 'terms', 'after_sales']);

it('links the legal pages from the footer', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="' . route('legal.privacy') . '"', false)
        ->assertSee('href="' . route('legal.terms') . '"', false)
        ->assertSee('href="' . route('legal.after_sales') . '"', false);
});

it('renders the french privacy page in french', function (): void {
    App::setLocale('fr');

    expect(view('front.pages.legal', ['page' => 'privacy'])->render())
        ->toContain('Politique de confidentialité')
        ->toContain('Autorité de protection des données');
});
