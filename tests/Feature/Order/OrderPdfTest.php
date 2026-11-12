<?php

declare(strict_types=1);

use App\Enum\CountryEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

it('names countries in the requested language', function (CountryEnum $country, string $locale, string $expected): void {
    expect($country->label($locale))->toBe($expected);
})->with([
    'Belgium in French' => [CountryEnum::BELGIUM, 'fr', 'Belgique'],
    'Belgium in Dutch' => [CountryEnum::BELGIUM, 'nl', 'België'],
    'Belgium in English' => [CountryEnum::BELGIUM, 'en', 'Belgium'],
    'Netherlands in Dutch' => [CountryEnum::NETHERLANDS, 'nl', 'Nederland'],
    'Réunion in French' => [CountryEnum::REUNION, 'fr', 'La Réunion'],
]);

it('uses the current locale by default', function (): void {
    App::setLocale('nl');

    expect(CountryEnum::FRANCE->label())->toBe('Frankrijk');
});

it('names a raw country code and keeps unknown codes as they are', function (): void {
    expect(CountryEnum::labelFor('LUX', 'fr'))->toBe('Luxembourg')
        ->and(CountryEnum::labelFor('XYZ', 'fr'))->toBe('XYZ')
        ->and(CountryEnum::labelFor(null, 'fr'))->toBe('');
});

it('renders the order pdf with country names, a local date and the right title', function (): void {
    $user = User::factory()->create(['role' => 'customer', 'language' => 'nl']);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'shipping_country' => 'FRA',
        'invoice_country' => 'BEL',
        'created_at' => Carbon::parse('2026-11-12 08:54:17'),
    ]);
    App::setLocale('nl');

    $html = view('pdf/order', ['order' => $order->fresh()])->render();

    expect($html)->toContain('<title>Cetronic Benelux</title>')
        ->toContain('Frankrijk')
        ->toContain('België')
        ->toContain('12/11/2026')
        ->not->toContain('2026-11-12 08:54:17')
        ->not->toMatch('/>\s*(FRA|BEL)\s*<br/');
});
