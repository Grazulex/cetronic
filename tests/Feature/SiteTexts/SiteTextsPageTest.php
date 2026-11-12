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
        ->assertSee('Location › Invoice — néerlandais');
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
        'en' => 'Any claim',
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
