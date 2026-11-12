<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/*
 * translations:export runs at every deploy (Forge script): it must rebuild
 * lang/ from the ltu_* tables exactly like the outhebox package did.
 */

beforeEach(function (): void {
    $this->langPath = useTemporaryLangPath();
});

afterEach(fn() => File::deleteDirectory($this->langPath));

it('writes one php file per language, with nested keys', function (): void {
    ltuPhrase('fr', 'listing', 'search.placeholder', 'Recherche');
    ltuPhrase('fr', 'listing', 'search.searching', 'Recherche en cours…');
    ltuPhrase('fr', 'listing', 'title', 'Produits');
    ltuPhrase('nl', 'listing', 'search.placeholder', 'Zoeken');

    $this->artisan('translations:export')->assertSuccessful();

    expect(require $this->langPath . '/fr/listing.php')->toBe([
        'search' => ['placeholder' => 'Recherche', 'searching' => 'Recherche en cours…'],
        'title' => 'Produits',
    ])->and(require $this->langPath . '/nl/listing.php')->toBe([
        'search' => ['placeholder' => 'Zoeken'],
    ]);
});

it('makes the exported texts available to the translator', function (): void {
    ltuPhrase('fr', 'listing', 'search.placeholder', 'Recherche');

    $this->artisan('translations:export')->assertSuccessful();
    // The translator was built at boot with the real lang path: rebuild it.
    $this->app->forgetInstance('translation.loader');
    $this->app->forgetInstance('translator');

    expect(__('listing.search.placeholder', [], 'fr'))->toBe('Recherche');
});

it('skips phrases without value but keeps empty strings', function (): void {
    ltuPhrase('fr', 'listing', 'missing', null);
    ltuPhrase('fr', 'listing', 'empty', '');

    $this->artisan('translations:export')->assertSuccessful();

    expect(require $this->langPath . '/fr/listing.php')->toBe(['empty' => '']);
});

it('writes json translation files as json', function (): void {
    ltuPhrase('fr', 'fr', 'Add to cart', 'Ajouter au panier', 'json');

    $this->artisan('translations:export')->assertSuccessful();

    expect(json_decode(File::get($this->langPath . '/fr/fr.json'), true))
        ->toBe(['Add to cart' => 'Ajouter au panier']);
});

it('replaces the content of exported files and leaves the other files alone', function (): void {
    File::ensureDirectoryExists($this->langPath . '/fr');
    File::put($this->langPath . '/fr/listing.php', "<?php\n\nreturn ['old' => 'Ancien'];\n");
    File::put($this->langPath . '/fr/auth.php', "<?php\n\nreturn ['failed' => 'Échec'];\n");
    ltuPhrase('fr', 'listing', 'title', 'Produits');

    $this->artisan('translations:export')->assertSuccessful();

    expect(require $this->langPath . '/fr/listing.php')->toBe(['title' => 'Produits'])
        ->and(require $this->langPath . '/fr/auth.php')->toBe(['failed' => 'Échec']);
});

it('escapes quotes so the generated files stay valid php', function (): void {
    ltuPhrase('fr', 'listing', 'quote', "L'article \"Montre\" \\ 5 €");

    $this->artisan('translations:export')->assertSuccessful();

    expect(require $this->langPath . '/fr/listing.php')->toBe(['quote' => "L'article \"Montre\" \\ 5 €"]);
});
