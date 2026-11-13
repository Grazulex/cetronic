<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/*
 * translations:import-missing runs at deploy time, before translations:export:
 * keys added by the developer in lang/ reach the database instead of being
 * wiped by the export, and the client's texts are never overwritten.
 */

beforeEach(function (): void {
    $this->langPath = useTemporaryLangPath();
    File::ensureDirectoryExists($this->langPath . '/en');
    File::ensureDirectoryExists($this->langPath . '/fr');
});

afterEach(fn() => File::deleteDirectory($this->langPath));

it('adds the keys missing in the database without touching the others', function (): void {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    File::put($this->langPath . '/en/cart.php', "<?php return ['title' => 'Basket', 'new' => ['label' => 'New']];");

    $this->artisan('translations:import-missing')->assertSuccessful();

    expect(DB::table('ltu_phrases')->where('key', 'new.label')->value('value'))->toBe('New')
        ->and(DB::table('ltu_phrases')->where('key', 'title')->value('value'))->toBe('Cart');
});

it('links a new french key to its english source', function (): void {
    File::put($this->langPath . '/en/cart.php', "<?php return ['new' => 'New'];");
    File::put($this->langPath . '/fr/cart.php', "<?php return ['new' => 'Nouveau'];");

    $this->artisan('translations:import-missing')->assertSuccessful();

    $en = DB::table('ltu_phrases')->where('value', 'New')->first();
    expect(DB::table('ltu_phrases')->where('value', 'Nouveau')->first())
        ->phrase_id->toBe($en->id)
        ->group->toBe('cart');
});

it('creates the translation file when it does not exist yet', function (): void {
    File::put($this->langPath . '/en/legal.php', "<?php return ['cgv' => ['title' => 'Terms']];");

    $this->artisan('translations:import-missing')->assertSuccessful();

    expect(DB::table('ltu_translation_files')->where('name', 'legal')->where('extension', 'php')->exists())->toBeTrue();
});

it('does nothing the second time', function (): void {
    File::put($this->langPath . '/en/cart.php', "<?php return ['new' => 'New'];");

    $this->artisan('translations:import-missing');
    $this->artisan('translations:import-missing')->expectsOutputToContain('0 key')->assertSuccessful();

    expect(DB::table('ltu_phrases')->count())->toBe(1);
});

it('ignores the languages of the package that the site does not use', function (): void {
    File::ensureDirectoryExists($this->langPath . '/de');
    File::put($this->langPath . '/de/cart.php', "<?php return ['new' => 'Neu'];");

    $this->artisan('translations:import-missing')->assertSuccessful();

    expect(DB::table('ltu_phrases')->count())->toBe(0);
});

it('keeps a new key through the export that follows', function (): void {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    File::put($this->langPath . '/en/cart.php', "<?php return ['title' => 'Cart', 'new' => 'New'];");

    $this->artisan('translations:import-missing');
    $this->artisan('translations:export');

    expect(require $this->langPath . '/en/cart.php')->toBe(['title' => 'Cart', 'new' => 'New']);
});
