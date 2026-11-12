<?php

declare(strict_types=1);

use App\SiteTexts\SiteText;
use App\SiteTexts\SiteTextFilter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

function siteText(
    string $fr = 'Panier',
    ?string $nl = 'Winkelwagen',
    ?string $en = 'Cart',
    string $label = 'Titre',
    string $zone = 'cart',
    ?CarbonImmutable $updatedAt = null,
    bool $long = false,
    string $key = 'title',
): SiteText {
    return new SiteText('cart', $key, $zone, $label, ['fr' => $fr, 'nl' => $nl, 'en' => $en], $long, $updatedAt);
}

/**
 * @param  list<SiteText>  $texts
 */
function filterTexts(array $texts, string $search = '', ?string $zone = null, string $show = 'all', bool $technical = false): Collection
{
    return app(SiteTextFilter::class)->apply(collect($texts), $search, $zone, $show, $technical);
}

it('searches the three languages without case', function (): void {
    expect(filterTexts([siteText(nl: 'Facturatieadres'), siteText(key: 'other')], search: 'facturatie'))->toHaveCount(1);
});

it('searches accented words without case', function (): void {
    expect(filterTexts([siteText(fr: 'Événement spécial')], search: 'ÉVÉNEMENT'))->toHaveCount(1);
});

it('searches the readable name', function (): void {
    expect(filterTexts([siteText(label: 'Adresse de facturation')], search: 'ADRESSE'))->toHaveCount(1);
});

it('searches the technical key', function (): void {
    expect(filterTexts([siteText(key: 'location.invoice')], search: 'cart.location'))->toHaveCount(1);
});

it('searches the text of long texts, not their tags', function (): void {
    expect(filterTexts([siteText(fr: '<p>Bonjour</p>', long: true)], search: '<p>'))->toBeEmpty()
        ->and(filterTexts([siteText(fr: '<p>Bonjour</p>', long: true)], search: 'bonjour'))->toHaveCount(1);
});

it('filters on one zone', function (): void {
    expect(filterTexts([siteText(zone: 'cart'), siteText(zone: 'order')], zone: 'order'))->toHaveCount(1);
});

it('lists a text empty in dutch as untranslated', function (): void {
    expect(filterTexts([siteText(nl: '')], show: 'untranslated'))->toHaveCount(1)
        ->and(filterTexts([siteText(nl: null)], show: 'untranslated'))->toHaveCount(1);
});

it('lists a dutch text identical to french as untranslated', function (): void {
    expect(filterTexts([siteText(fr: 'Panier', nl: 'Panier')], show: 'untranslated'))->toHaveCount(1);
});

it('does not list a fully translated text as untranslated', function (): void {
    expect(filterTexts([siteText()], show: 'untranslated'))->toBeEmpty();
});

it('ignores the configured exceptions and numbers', function (): void {
    config(['site-texts.untranslated_exceptions' => ['Best Seller']]);

    expect(filterTexts([
        siteText(fr: 'Best Seller', nl: 'Best Seller', en: 'Best Seller'),
        siteText(fr: '24', nl: '24', en: '24', key: 'hours'),
    ], show: 'untranslated'))->toBeEmpty();
});

it('keeps texts changed in the last 30 days', function (): void {
    expect(filterTexts([
        siteText(updatedAt: CarbonImmutable::now()->subDays(31)),
        siteText(updatedAt: CarbonImmutable::now()->subDays(2), key: 'recent'),
        siteText(updatedAt: null, key: 'never'),
    ], show: 'recent'))->toHaveCount(1);
});

it('hides technical zones by default', function (): void {
    expect(filterTexts([siteText(zone: 'validation')]))->toBeEmpty()
        ->and(filterTexts([siteText(zone: 'validation')], technical: true))->toHaveCount(1)
        ->and(filterTexts([siteText(zone: 'validation')], zone: 'validation'))->toHaveCount(1);
});

it('tells which fields match the search', function (): void {
    expect(app(SiteTextFilter::class)->matches(siteText(nl: 'Facturatie'), 'factur'))
        ->toBe(['label' => false, 'fr' => false, 'nl' => true, 'en' => false]);
});

it('matches nothing for an empty search', function (): void {
    expect(app(SiteTextFilter::class)->matches(siteText(), ''))
        ->toBe(['label' => false, 'fr' => false, 'nl' => false, 'en' => false]);
});
