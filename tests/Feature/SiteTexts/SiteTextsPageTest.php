<?php

declare(strict_types=1);

use App\Filament\Pages\SiteTexts;
use App\SiteTexts\SiteText;
use App\SiteTexts\SiteTextRepository;
use Illuminate\Support\Facades\File;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->langPath = useTemporaryLangPath();
});

afterEach(fn() => File::deleteDirectory($this->langPath));

it('is reserved to admins', function (string $role, int $status): void {
    'admin' === $role ? loginAsAdmin() : ('agent' === $role ? loginAsAgent() : loginAsUser());

    $this->get(SiteTexts::getUrl())->assertStatus($status);
})->with([
    'admin' => ['admin', 200],
    'agent' => ['agent', 403],
    'customer' => ['customer', 403],
]);

it('is listed in the content navigation group', function (): void {
    loginAsAdmin();

    expect(SiteTexts::getNavigationGroup())->toBe('Contenu')
        ->and(SiteTexts::getNavigationLabel())->toBe('Textes du site')
        ->and(SiteTexts::getUrl())->toEndWith('/admin/textes-du-site');
});

it('shows the zones closed with their count', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'order', 'location.invoice', 'Invoice address');
    ltuPhrase('en', 'order', 'total', 'Total');

    livewire(SiteTexts::class)
        ->assertSee('PDF bon de commande (2)')
        ->assertDontSee('Invoice address');
});

it('shows the three languages of an opened zone', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'order', 'location.invoice', 'Invoice address');
    ltuPhrase('fr', 'order', 'location.invoice', 'Adresse de facturation');
    $id = SiteText::idFor('order', 'location.invoice');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'order')
        ->assertSet("drafts.{$id}", ['fr' => 'Adresse de facturation', 'nl' => null, 'en' => 'Invoice address'])
        ->assertSee('Adresse de facturation — néerlandais');
});

it('saves a row whose key contains dots and publishes it', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'order', 'location.invoice', 'Invoice address');
    $id = SiteText::idFor('order', 'location.invoice');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'order')
        ->set("drafts.{$id}.nl", 'Factuuradres')
        ->call('saveRow', $id)
        ->assertNotified('Texte enregistré et publié');

    expect(app(SiteTextRepository::class)->find($id)->values['nl'])->toBe('Factuuradres')
        ->and(require $this->langPath . '/nl/order.php')->toBe(['location' => ['invoice' => 'Factuuradres']]);
});

it('warns when the text is saved but not published', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'order', 'total', 'Total');
    $id = SiteText::idFor('order', 'total');
    File::put($this->langPath . '/blocked', '');
    app()->useLangPath($this->langPath . '/blocked');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'order')
        ->set("drafts.{$id}.fr", 'Total')
        ->call('saveRow', $id)
        ->assertNotified();

    expect(app(SiteTextRepository::class)->find($id)->values['fr'])->toBe('Total');
});

it('opens the zones that match the search', function (): void {
    loginAsAdmin();
    ltuPhrase('nl', 'order', 'location.invoice', 'Factuuradres');
    ltuPhrase('nl', 'cart', 'title', 'Winkelwagen');

    livewire(SiteTexts::class)
        ->set('search', 'factuur')
        ->assertSet('openZones', ['order'])
        ->assertSee('PDF bon de commande (1)')
        ->assertDontSee('Panier et commande (');
});

it('lists only the texts to translate', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'order', 'total', 'Order total');
    ltuPhrase('fr', 'order', 'total', 'Total commande');
    ltuPhrase('nl', 'order', 'total', 'Totaal');
    ltuPhrase('en', 'cart', 'title', 'Cart');

    livewire(SiteTexts::class)
        ->set('show', 'untranslated')
        ->assertSee('Panier et commande (1)')
        ->assertDontSee('PDF bon de commande (');
});

it('hides the technical texts until asked', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'validation', 'required', 'The :attribute field is required.');

    livewire(SiteTexts::class)
        ->assertDontSee('Messages d’erreur des formulaires')
        ->set('technical', true)
        ->assertSee('Messages d’erreur des formulaires (1)');
});

it('edits a long text in the rich editor, not in a plain field', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'about', 'section1.description', '<p>Any claim</p>');
    $id = SiteText::idFor('about', 'section1.description');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'about')
        ->assertDontSeeHtml("drafts.{$id}.en")
        ->assertSee('Modifier le texte')
        ->callAction('editLongText', [
            'fr' => '<p><strong>Toute</strong> réclamation<script>x()</script></p><p>Par écrit</p>',
            'nl' => '<p>Elke klacht</p>',
            'en' => '<p>Any claim</p>',
        ], arguments: ['id' => $id])
        ->assertHasNoActionErrors()
        ->assertNotified('Texte enregistré et publié');

    expect(app(SiteTextRepository::class)->find($id)->values)->toBe([
        'fr' => '<p><strong>Toute</strong> réclamation</p><p>Par écrit</p>',
        'nl' => 'Elke klacht',
        'en' => '<p>Any claim</p>', // untouched in the editor: not rewritten
    ]);
});

it('opens the rich editor with paragraphs instead of double line breaks', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'emails', 'order_new.body', 'Hello,<br><br>Thanks');
    $id = SiteText::idFor('emails', 'order_new.body');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'emails')
        ->mountAction('editLongText', ['id' => $id])
        ->assertActionDataSet(['en' => '<p>Hello,</p><p>Thanks</p>']);
});

// --- Final review fixes -----------------------------------------------------

it('fills every row of a zone left open after the search is cleared', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'cart', 'shipping.title', 'Total shipping');
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $other = SiteText::idFor('cart', 'title');

    livewire(SiteTexts::class)
        ->set('search', 'shipping')
        ->set('search', '')
        ->assertSet("drafts.{$other}", ['fr' => null, 'nl' => null, 'en' => 'Cart']);
});

it('fills the rows shown after switching back to all texts', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'cart', 'title', 'Cart');
    ltuPhrase('fr', 'cart', 'title', 'Panier');
    ltuPhrase('nl', 'cart', 'title', 'Winkelwagen');
    ltuPhrase('en', 'cart', 'empty', 'Empty');
    $translated = SiteText::idFor('cart', 'title');

    livewire(SiteTexts::class)
        ->set('show', 'untranslated')
        ->call('toggleZone', 'cart')
        ->set('show', 'all')
        ->assertSet("drafts.{$translated}.nl", 'Winkelwagen');
});

it('does not overwrite a language changed elsewhere since the zone was opened', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'cart', 'title', 'Cart');
    ltuPhrase('fr', 'cart', 'title', 'Panier');
    $nl = ltuPhrase('nl', 'cart', 'title', 'Wagen');
    $id = SiteText::idFor('cart', 'title');

    $page = livewire(SiteTexts::class)->call('toggleZone', 'cart');
    DB::table('ltu_phrases')->where('id', $nl)->update(['value' => 'Winkelwagen']);

    $page->set("drafts.{$id}.fr", 'Votre panier')->call('saveRow', $id);

    expect(app(SiteTextRepository::class)->find($id)->values)
        ->toBe(['fr' => 'Votre panier', 'nl' => 'Winkelwagen', 'en' => 'Cart']);
});

it('refuses to save a language that was changed elsewhere meanwhile', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $fr = ltuPhrase('fr', 'cart', 'title', 'Panier');
    $id = SiteText::idFor('cart', 'title');

    $page = livewire(SiteTexts::class)->call('toggleZone', 'cart');
    DB::table('ltu_phrases')->where('id', $fr)->update(['value' => 'Mon panier']);

    $page->set("drafts.{$id}.fr", 'Votre panier')
        ->call('saveRow', $id)
        ->assertNotified('Ce texte a été modifié entre-temps')
        ->assertSet("drafts.{$id}.fr", 'Mon panier');

    expect(app(SiteTextRepository::class)->find($id)->values['fr'])->toBe('Mon panier');
});

it('does not create an empty translation for an untouched missing language', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $id = SiteText::idFor('cart', 'title');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'cart')
        ->set("drafts.{$id}.nl", '')
        ->set("drafts.{$id}.fr", 'Panier')
        ->call('saveRow', $id);

    expect(app(SiteTextRepository::class)->find($id)->values['nl'])->toBeNull();
});

it('does not create an empty translation from the rich editor', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'emails', 'user_welcome.body', '<p>Welcome :name</p><p>Team</p>');
    $id = SiteText::idFor('emails', 'user_welcome.body');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'emails')
        ->callAction('editLongText', ['fr' => '<p>Bienvenue :name</p><p>L’équipe</p>', 'nl' => '', 'en' => '<p>Welcome :name</p><p>Team</p>'], arguments: ['id' => $id]);

    expect(app(SiteTextRepository::class)->find($id)->values['nl'])->toBeNull();
});

it('only rewrites the languages changed in the rich editor', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'emails', 'user_welcome.body', 'Welcome,<br><br>Team');
    DB::table('ltu_phrases')->update(['updated_at' => '2026-01-01 00:00:00']);
    $id = SiteText::idFor('emails', 'user_welcome.body');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'emails')
        ->callAction('editLongText', ['fr' => '<p>Bienvenue</p><p>L’équipe</p>', 'nl' => '', 'en' => '<p>Welcome,</p><p>Team</p>'], arguments: ['id' => $id]);

    expect(DB::table('ltu_phrases')->where('value', 'Welcome,<br><br>Team')->value('updated_at'))->toBe('2026-01-01 00:00:00');
});

it('never saves a long text through the plain row save', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'listing', 'promo.text', '<p>Promo</p><p>Now</p>');
    $id = SiteText::idFor('listing', 'promo.text');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'listing')
        ->set("drafts.{$id}.en", '<p>Promo</p><script>alert(1)</script>')
        ->call('saveRow', $id);

    expect(app(SiteTextRepository::class)->find($id)->values['en'])->toBe('<p>Promo</p><p>Now</p>');
});

it('cleans html typed in a short text', function (): void {
    loginAsAdmin();
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $id = SiteText::idFor('cart', 'title');

    livewire(SiteTexts::class)
        ->call('toggleZone', 'cart')
        ->set("drafts.{$id}.fr", 'Panier<script>alert(1)</script>')
        ->call('saveRow', $id);

    expect(app(SiteTextRepository::class)->find($id)->values['fr'])->toBe('Panier');
});
