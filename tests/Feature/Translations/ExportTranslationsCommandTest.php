<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/*
 * translations:export runs at every deploy (Forge script): it must rebuild
 * lang/ from the ltu_* tables exactly like the outhebox package did.
 */

beforeEach(function (): void {
    $this->langPath = sys_get_temp_dir() . '/cetronic-lang-' . Str::random(8);
    File::ensureDirectoryExists($this->langPath);
    $this->app->useLangPath($this->langPath);

    $fr = DB::table('ltu_languages')->insertGetId(['name' => 'French', 'code' => 'fr']);
    $nl = DB::table('ltu_languages')->insertGetId(['name' => 'Dutch', 'code' => 'nl']);
    $this->fr = DB::table('ltu_translations')->insertGetId(['language_id' => $fr]);
    $this->nl = DB::table('ltu_translations')->insertGetId(['language_id' => $nl]);
    $this->listing = DB::table('ltu_translation_files')->insertGetId(['name' => 'listing', 'extension' => 'php']);
    $this->json = DB::table('ltu_translation_files')->insertGetId(['name' => 'fr', 'extension' => 'json']);
});

afterEach(fn() => File::deleteDirectory($this->langPath));

function phrase(int $translation, int $file, string $key, ?string $value): void
{
    DB::table('ltu_phrases')->insert([
        'uuid' => (string) Str::uuid(),
        'translation_id' => $translation,
        'translation_file_id' => $file,
        'key' => $key,
        'group' => 'listing',
        'value' => $value,
    ]);
}

it('writes one php file per language, with nested keys', function (): void {
    phrase($this->fr, $this->listing, 'search.placeholder', 'Recherche');
    phrase($this->fr, $this->listing, 'search.searching', 'Recherche en cours…');
    phrase($this->fr, $this->listing, 'title', 'Produits');
    phrase($this->nl, $this->listing, 'search.placeholder', 'Zoeken');

    $this->artisan('translations:export')->assertSuccessful();

    expect(require $this->langPath . '/fr/listing.php')->toBe([
        'search' => ['placeholder' => 'Recherche', 'searching' => 'Recherche en cours…'],
        'title' => 'Produits',
    ])->and(require $this->langPath . '/nl/listing.php')->toBe([
        'search' => ['placeholder' => 'Zoeken'],
    ]);
});

it('makes the exported texts available to the translator', function (): void {
    phrase($this->fr, $this->listing, 'search.placeholder', 'Recherche');

    $this->artisan('translations:export')->assertSuccessful();
    // The translator was built at boot with the real lang path: rebuild it.
    $this->app->forgetInstance('translation.loader');
    $this->app->forgetInstance('translator');

    expect(__('listing.search.placeholder', [], 'fr'))->toBe('Recherche');
});

it('skips phrases without value but keeps empty strings', function (): void {
    phrase($this->fr, $this->listing, 'missing', null);
    phrase($this->fr, $this->listing, 'empty', '');

    $this->artisan('translations:export')->assertSuccessful();

    expect(require $this->langPath . '/fr/listing.php')->toBe(['empty' => '']);
});

it('writes json translation files as json', function (): void {
    phrase($this->fr, $this->json, 'Add to cart', 'Ajouter au panier');

    $this->artisan('translations:export')->assertSuccessful();

    expect(json_decode(File::get($this->langPath . '/fr/fr.json'), true))
        ->toBe(['Add to cart' => 'Ajouter au panier']);
});

it('replaces the content of exported files and leaves the other files alone', function (): void {
    File::ensureDirectoryExists($this->langPath . '/fr');
    File::put($this->langPath . '/fr/listing.php', "<?php\n\nreturn ['old' => 'Ancien'];\n");
    File::put($this->langPath . '/fr/auth.php', "<?php\n\nreturn ['failed' => 'Échec'];\n");
    phrase($this->fr, $this->listing, 'title', 'Produits');

    $this->artisan('translations:export')->assertSuccessful();

    expect(require $this->langPath . '/fr/listing.php')->toBe(['title' => 'Produits'])
        ->and(require $this->langPath . '/fr/auth.php')->toBe(['failed' => 'Échec']);
});

it('escapes quotes so the generated files stay valid php', function (): void {
    phrase($this->fr, $this->listing, 'quote', "L'article \"Montre\" \\ 5 €");

    $this->artisan('translations:export')->assertSuccessful();

    expect(require $this->langPath . '/fr/listing.php')->toBe(['quote' => "L'article \"Montre\" \\ 5 €"]);
});
