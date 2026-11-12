<?php

declare(strict_types=1);

use App\SiteTexts\SiteText;
use App\SiteTexts\SiteTextRepository;

it('groups the three languages of a key in one text', function (): void {
    ltuPhrase('en', 'order', 'location.invoice', 'Invoice address');
    ltuPhrase('fr', 'order', 'location.invoice', 'Adresse de facturation');
    ltuPhrase('nl', 'order', 'location.invoice', 'Factuuradres');

    $text = app(SiteTextRepository::class)->all()->sole();

    expect($text->values)->toBe(['fr' => 'Adresse de facturation', 'nl' => 'Factuuradres', 'en' => 'Invoice address'])
        ->and($text->zone)->toBe('order')
        ->and($text->label)->toBe('Location › Invoice')
        ->and($text->fullKey())->toBe('order.location.invoice');
});

it('returns null for a language without row', function (): void {
    ltuPhrase('en', 'cart', 'title', 'Cart');

    expect(app(SiteTextRepository::class)->all()->sole()->values['nl'])->toBeNull();
});

it('keeps a text that only exists in french', function (): void {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    ltuPhrase('fr', 'cart', 'promo', 'Promo');

    expect(app(SiteTextRepository::class)->all()->map(fn(SiteText $t): string => $t->key)->sort()->values()->all())
        ->toBe(['promo', 'title']);
});

it('detects long texts from their html', function (): void {
    ltuPhrase('en', 'legal', 'cgv.claims', '<p>Any claim…</p>');
    ltuPhrase('en', 'legal', 'cgv.title', 'Terms < 5 > 3');

    $texts = app(SiteTextRepository::class)->all()->keyBy('key');

    expect($texts['cgv.claims']->long)->toBeTrue()
        ->and($texts['cgv.title']->long)->toBeFalse();
});

it('ignores json translation files', function (): void {
    ltuPhrase('fr', 'fr', 'Add to cart', 'Ajouter au panier', 'json');

    expect(app(SiteTextRepository::class)->all())->toBeEmpty();
});

it('finds a text by its id', function (): void {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $id = app(SiteTextRepository::class)->all()->sole()->id();

    expect(app(SiteTextRepository::class)->find($id)?->key)->toBe('title')
        ->and(app(SiteTextRepository::class)->find('unknown'))->toBeNull();
});

it('gives the latest update of the three languages', function (): void {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $fr = ltuPhrase('fr', 'cart', 'title', 'Panier');
    DB::table('ltu_phrases')->update(['updated_at' => '2026-01-01 10:00:00']);
    DB::table('ltu_phrases')->where('id', $fr)->update(['updated_at' => '2026-03-05 08:00:00']);

    expect(app(SiteTextRepository::class)->all()->sole()->updatedAt?->toDateTimeString())->toBe('2026-03-05 08:00:00');
});
