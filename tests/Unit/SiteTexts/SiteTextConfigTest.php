<?php

declare(strict_types=1);

use App\SiteTexts\SiteTextConfig;

it('puts the footer keys of home in the footer zone', function (): void {
    expect(app(SiteTextConfig::class)->zoneFor('home', 'footer.column4.address'))->toBe('footer');
});

it('puts the other home keys in the home zone', function (): void {
    expect(app(SiteTextConfig::class)->zoneFor('home', 'banner.title'))->toBe('home');
});

it('sends an unknown file to the other zone', function (): void {
    expect(app(SiteTextConfig::class)->zoneFor('title', 'x'))->toBe('other');
});

it('derives a readable name from the key', function (): void {
    expect(app(SiteTextConfig::class)->labelFor('order', 'location.unknown_place'))->toBe('Location › Unknown place');
});

it('turns underscores and dashes into spaces in derived names', function (): void {
    expect(app(SiteTextConfig::class)->labelFor('emails', 'order_new.body-text'))->toBe('Order new › Body text');
});

it('prefers the configured name', function (): void {
    config(['site-texts.labels' => ['order.location.invoice' => 'Adresse de facturation']]);

    expect(app(SiteTextConfig::class)->labelFor('order', 'location.invoice'))->toBe('Adresse de facturation');
});

it('flags validation as technical', function (): void {
    expect(app(SiteTextConfig::class)->zones()['validation']['technical'])->toBeTrue()
        ->and(app(SiteTextConfig::class)->zones()['cart']['technical'])->toBeFalse();
});

it('sorts the zones by their order', function (): void {
    $orders = array_column(app(SiteTextConfig::class)->zones(), 'order');

    expect($orders)->toBe(collect($orders)->sort()->values()->all());
});

it('knows the long texts forced in the configuration', function (): void {
    config(['site-texts.long' => ['legal.cgv.claims']]);

    expect(app(SiteTextConfig::class)->isLongForced('legal', 'cgv.claims'))->toBeTrue()
        ->and(app(SiteTextConfig::class)->isLongForced('legal', 'cgv.title'))->toBeFalse();
});

it('only names keys of known zones', function (): void {
    $config = app(SiteTextConfig::class);

    $orphans = collect(config('site-texts.labels'))->keys()->filter(function (string $fullKey) use ($config): bool {
        [$file, $key] = explode('.', $fullKey, 2) + [1 => ''];

        return SiteTextConfig::FALLBACK_ZONE === $config->zoneFor($file, $key) || '' === $key;
    });

    expect($orphans->values()->all())->toBe([]);
});
