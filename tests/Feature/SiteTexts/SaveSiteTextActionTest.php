<?php

declare(strict_types=1);

use App\SiteTexts\SaveSiteTextAction;
use App\SiteTexts\SiteTextRepository;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->langPath = useTemporaryLangPath();
});

afterEach(fn() => File::deleteDirectory($this->langPath));

it('publishes the new value to the lang files', function (): void {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $text = app(SiteTextRepository::class)->all()->sole();

    $result = app(SaveSiteTextAction::class)->handle($text, ['fr' => 'Panier']);

    expect($result->published)->toBeTrue()
        ->and($result->error)->toBeNull()
        ->and($result->text->values['fr'])->toBe('Panier')
        ->and(require $this->langPath . '/fr/cart.php')->toBe(['title' => 'Panier']);
});

it('keeps the value when the export fails', function (): void {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    // A file where the lang directory should be: the export cannot write.
    File::put($this->langPath . '/blocked', '');
    app()->useLangPath($this->langPath . '/blocked');

    $result = app(SaveSiteTextAction::class)->handle(app(SiteTextRepository::class)->all()->sole(), ['fr' => 'Panier']);

    expect($result->published)->toBeFalse()
        ->and($result->error)->not->toBeEmpty()
        ->and($result->text->values['fr'])->toBe('Panier')
        ->and(app(SiteTextRepository::class)->all()->sole()->values['fr'])->toBe('Panier');
});
