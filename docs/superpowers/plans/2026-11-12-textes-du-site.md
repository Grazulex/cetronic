# Textes du site (étapes 1 à 3) — plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal :** une page Filament « Textes du site » où un admin modifie les textes du site dans les trois langues côte à côte, avec publication immédiate, recherche, filtres et éditeur riche pour les textes longs.

**Architecture :** les textes restent dans les tables `ltu_*` (source de vérité). Un petit domaine `App\SiteTexts` lit ces tables en objets `SiteText` (une clé = trois langues), les filtre en mémoire (≈ 500 clés), les enregistre et appelle `ExportTranslationsAction` pour régénérer `lang/`. La page Filament n'affiche que les zones ouvertes et n'enregistre qu'une ligne à la fois.

**Tech stack :** Laravel 10, Filament 3.3, Livewire 3.8, Pest, `symfony/html-sanitizer` (déjà installé via Filament, à déclarer).

**Spec :** `docs/specs/2026-11-12-textes-du-site.md` — ce plan couvre les étapes 1, 2 et 3 du § 9. Les étapes 4 (bouton « Traduire cette page », index des usages) et 5 (variables, pluriels, historique) feront l'objet d'un second plan.

**Branche :** `feat/site-texts-editor`, créée depuis `feat/filament-v3-livewire-3`. Les deux branches seront fusionnées ensemble (la migration retire l'interface `/translations`).

## Global Constraints

- Langues affichées dans cet ordre : **FR, NL, EN**. `en` est la langue source du package (`ltu_translations.source = 1`) ; les lignes `fr`/`nl` pointent vers la ligne `en` via `phrase_id`.
- Accès : rôle `admin` uniquement (`UserRoleEnum::ADMIN`). Agent et client : 403.
- Navigation : nouveau groupe **« Contenu »**, libellé **« Textes du site »**, slug `textes-du-site`.
- Message après enregistrement : **« Texte enregistré et publié »**.
- « Modifiés récemment » = `updated_at` dans les **30 jours**.
- Zone `validation` masquée par défaut, case **« Afficher les textes techniques »**.
- Éditeur riche : barre **gras, italique, lien, liste à puces, liste numérotée** ; balises conservées : `p`, `br`, `strong`, `em`, `a[href]`, `ul`, `ol`, `li`.
- Production en PHP 8.3 : pas de syntaxe 8.4 ; après Pint, vérifier l'absence de `mb_trim(`/`mb_ltrim(`/`mb_rtrim(` dans le diff.
- Dépendances : `composer install --no-dev` en prod, donc toute dépendance runtime va dans `require`.
- Pint uniquement sur les fichiers créés ou réécrits ; `composer`-scripts inexistants : lancer `./vendor/bin/pest` et `./vendor/bin/phpstan analyse --memory-limit=2G`.

## Review Focus

1. **Clés avec des points dans les chemins Livewire** (`order.location.invoice`) : un `wire:model="drafts.order.location.invoice.fr"` serait interprété comme un tableau imbriqué. Les lignes sont adressées par un identifiant haché (`SiteText::id()`), jamais par la clé. Test dans la tâche 6.
2. **Langue sans ligne en base** (4 lignes FR/NL orphelines, clés sans traduction NL) : enregistrer une valeur doit créer la ligne manquante, liée à la ligne `en` par `phrase_id`. Test dans la tâche 3.
3. **Enregistrer une ligne ne doit toucher que les langues modifiées** : sinon `updated_at` change partout et « Modifiés récemment » devient faux. Test dans la tâche 3.
4. **Recherche avec majuscules et accents** (« facturatie », « Événement ») : insensible à la casse, multioctet. Test dans la tâche 4.
5. **Texte long ouvert dans un champ court** : un texte contenant du HTML ne doit jamais s'afficher en champ texte brut ; il passe par la fenêtre d'édition riche. Test dans la tâche 7.

---

## Structure des fichiers

| Fichier | Rôle |
|---|---|
| `config/site-texts.php` | Zones (libellé, ordre, groupes/préfixes), noms lisibles, textes longs forcés, exceptions « À traduire », zones techniques |
| `app/SiteTexts/SiteTextConfig.php` | Lecture de la config : zone d'une clé, nom lisible, long ou non |
| `app/SiteTexts/SiteText.php` | Objet valeur : un texte = fichier + clé + trois valeurs |
| `app/SiteTexts/SiteTextRepository.php` | Lecture et écriture des tables `ltu_*` |
| `app/SiteTexts/SiteTextFilter.php` | Recherche, zone, « À traduire », « Modifiés récemment », textes techniques |
| `app/SiteTexts/SaveSiteTextAction.php` | Enregistre puis publie (export), signale un échec d'export |
| `app/SiteTexts/RichTextCleaner.php` | Nettoyage HTML + conversion `<br><br>` en paragraphes |
| `app/Filament/Pages/SiteTexts.php` + `resources/views/filament/pages/site-texts.blade.php` | La page |
| `app/Console/Commands/ImportMissingTranslationsCommand.php` | Ajoute en base les clés présentes dans `lang/` mais absentes de la base |

---

### Task 1: Configuration des zones et noms lisibles

**Files:**
- Create: `config/site-texts.php`, `app/SiteTexts/SiteTextConfig.php`
- Test: `tests/Unit/SiteTexts/SiteTextConfigTest.php` (ajouter `uses(Tests\TestCase::class)->in('Unit/SiteTexts')` dans `tests/Pest.php` pour avoir `config()`)

**Interfaces:**
- Produces:
  - `SiteTextConfig::zoneFor(string $file, string $key): string` — identifiant de zone.
  - `SiteTextConfig::zones(): array<string, array{label: string, order: int, technical: bool}>` triées par `order`.
  - `SiteTextConfig::labelFor(string $file, string $key): string`.
  - `SiteTextConfig::isLongForced(string $file, string $key): bool`.
  - `SiteTextConfig::untranslatedExceptions(): list<string>`.

Format de `config/site-texts.php` :

```php
return [
    'zones' => [
        // id => libellé, ordre, puis règles: fichier entier, ou fichier + préfixe de clé
        'footer' => ['label' => 'Pied de page', 'order' => 20, 'match' => ['home' => 'footer.']],
        'home' => ['label' => "Page d'accueil", 'order' => 10, 'match' => ['home' => '']],
        // ... une entrée par ligne du tableau § 4.3 de la spec,
        //     plus 'other' => ['label' => 'Autres textes', 'order' => 999, 'match' => []]
        'validation' => ['label' => 'Messages d\'erreur des formulaires', 'order' => 900, 'match' => ['validation' => ''], 'technical' => true],
    ],
    'labels' => [ /* 'order.location.invoice' => 'Adresse de facturation', ... (tâche 8) */ ],
    'long' => [ /* clés forcées en texte long, format 'fichier.clé' */ ],
    'untranslated_exceptions' => ['Cetronic', 'Best Seller'],
];
```

Règle de zone : la règle de préfixe la plus longue gagne (`home` + `footer.` avant `home` + `''`). Un fichier sans règle va dans `other`. Les fichiers `description` et `title` (présents en base) vont dans `other` tant que personne ne les classe.

- [ ] **Step 1: Write the failing tests**

```php
it('puts the footer keys of home in the footer zone', fn () =>
    expect(app(SiteTextConfig::class)->zoneFor('home', 'footer.column4.address'))->toBe('footer'));
it('puts the other home keys in the home zone', fn () =>
    expect(app(SiteTextConfig::class)->zoneFor('home', 'banner.title'))->toBe('home'));
it('sends an unknown file to the other zone', fn () =>
    expect(app(SiteTextConfig::class)->zoneFor('title', 'x'))->toBe('other'));
it('derives a readable name from the key', fn () =>
    expect(app(SiteTextConfig::class)->labelFor('order', 'location.invoice'))->toBe('Location › Invoice'));
it('prefers the configured name', function () {
    config(['site-texts.labels' => ['order.location.invoice' => 'Adresse de facturation']]);
    expect(app(SiteTextConfig::class)->labelFor('order', 'location.invoice'))->toBe('Adresse de facturation');
});
it('flags validation as technical', fn () =>
    expect(app(SiteTextConfig::class)->zones()['validation']['technical'])->toBeTrue());
```

Nom déduit : segments de la clé séparés par `.`, `_` et `-` remplacés par des espaces, première lettre en majuscule (`Str::ucfirst`), joints par ` › `.

- [ ] **Step 2: Run** `./vendor/bin/pest tests/Unit/SiteTexts/SiteTextConfigTest.php` — attendu : FAIL (classe absente).
- [ ] **Step 3: Implement** `config/site-texts.php` (toutes les zones du § 4.3, labels vides pour l'instant) et `SiteTextConfig` (lit `config('site-texts')`, classe `final`, sans état).
- [ ] **Step 4: Run** le même test — attendu : PASS.
- [ ] **Step 5: Commit** `feat: add site texts zones configuration`

---

### Task 2: Lire les textes depuis la base

**Files:**
- Create: `app/SiteTexts/SiteText.php`, `app/SiteTexts/SiteTextRepository.php`
- Test: `tests/Feature/SiteTexts/SiteTextRepositoryTest.php`

**Interfaces:**
- Consumes: `SiteTextConfig` (tâche 1).
- Produces:
  - `final readonly class SiteText` : `string $file`, `string $key`, `string $zone`, `string $label`, `array<string, ?string> $values` (clés `fr`, `nl`, `en`, toujours présentes, `null` si pas de ligne ou valeur NULL), `bool $long`, `?CarbonImmutable $updatedAt` (le plus récent des trois).
  - `SiteText::LOCALES = ['fr', 'nl', 'en']`.
  - `SiteText::id(): string` — `substr(sha1($file . ':' . $key), 0, 12)`.
  - `SiteText::fullKey(): string` — `"{$file}.{$key}"`.
  - `SiteTextRepository::all(): Collection<int, SiteText>` triée par ordre de zone, puis libellé.
  - `SiteTextRepository::find(string $id): ?SiteText`.

`long` = `isLongForced()` ou une des valeurs contient une balise (`preg_match('/<[a-z][^>]*>/i', …)`).

Lecture : une requête sur `ltu_phrases` jointe à `ltu_translations` → `ltu_languages` (pour le code langue) et `ltu_translation_files` (pour le nom du fichier). Regrouper en PHP par `fichier + clé`. Seules les extensions `php` sont éditées (les `json` sont ignorés ici).

- [ ] **Step 1: Write the failing tests** (seed : reprendre le helper `phrase()` de `tests/Feature/Translations/ExportTranslationsCommandTest.php`, en le déplaçant dans `tests/Pest.php` sous le nom `ltuPhrase(string $locale, string $file, string $key, ?string $value): int` qui crée langue, traduction et fichier au besoin et lie `fr`/`nl` à la ligne `en` via `phrase_id`).

```php
it('groups the three languages of a key in one text', function () {
    ltuPhrase('en', 'order', 'location.invoice', 'Invoice address');
    ltuPhrase('fr', 'order', 'location.invoice', 'Adresse de facturation');
    ltuPhrase('nl', 'order', 'location.invoice', 'Factuuradres');

    $text = app(SiteTextRepository::class)->all()->sole();

    expect($text->values)->toBe(['fr' => 'Adresse de facturation', 'nl' => 'Factuuradres', 'en' => 'Invoice address'])
        ->and($text->zone)->toBe('order');
});
it('returns null for a language without row', function () {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    expect(app(SiteTextRepository::class)->all()->sole()->values['nl'])->toBeNull();
});
it('detects long texts from their html', function () {
    ltuPhrase('en', 'legal', 'cgv.claims', '<p>Any claim…</p>');
    expect(app(SiteTextRepository::class)->all()->sole()->long)->toBeTrue();
});
it('finds a text by its id', function () {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $id = app(SiteTextRepository::class)->all()->sole()->id();
    expect(app(SiteTextRepository::class)->find($id)?->key)->toBe('title');
});
```

- [ ] **Step 2: Run** `./vendor/bin/pest tests/Feature/SiteTexts/SiteTextRepositoryTest.php` — attendu : FAIL.
- [ ] **Step 3: Implement** `SiteText` et `SiteTextRepository::all()/find()`.
- [ ] **Step 4: Run** — attendu : PASS. Relancer aussi `tests/Feature/Translations` (le helper a bougé).
- [ ] **Step 5: Commit** `feat: read site texts from the translation tables`

---

### Task 3: Enregistrer un texte

**Files:**
- Modify: `app/SiteTexts/SiteTextRepository.php`
- Test: `tests/Feature/SiteTexts/SiteTextRepositoryTest.php`

**Interfaces:**
- Produces: `SiteTextRepository::save(SiteText $text, array<string, ?string> $values): SiteText` — `$values` indexé par langue (sous-ensemble de `fr`, `nl`, `en`) ; retourne le texte relu.

Règles :
- une langue dont la valeur ne change pas n'est **pas** écrite (`updated_at` intact) ;
- `''` est enregistré tel quel (texte vide signalé, pas supprimé) ;
- une langue sans ligne : insérer `uuid` (`Str::uuid()`), `translation_id` de la langue, `translation_file_id` du fichier, `phrase_id` = id de la ligne `en` de la même clé (ou `null` s'il n'y en a pas), `key`, `group` = nom du fichier, `value`, timestamps.

- [ ] **Step 1: Write the failing tests**

```php
it('updates only the changed language', function () {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $fr = ltuPhrase('fr', 'cart', 'title', 'Panier');
    DB::table('ltu_phrases')->update(['updated_at' => '2026-01-01 00:00:00']);
    $repo = app(SiteTextRepository::class);

    $repo->save($repo->all()->sole(), ['fr' => 'Votre panier', 'en' => 'Cart']);

    expect(DB::table('ltu_phrases')->find($fr)->value)->toBe('Votre panier')
        ->and(DB::table('ltu_phrases')->where('value', 'Cart')->value('updated_at'))->toBe('2026-01-01 00:00:00');
});
it('creates the missing language row linked to the source phrase', function () {
    $en = ltuPhrase('en', 'cart', 'title', 'Cart');
    $repo = app(SiteTextRepository::class);

    $repo->save($repo->all()->sole(), ['nl' => 'Winkelwagen']);

    expect(DB::table('ltu_phrases')->where('value', 'Winkelwagen')->first())
        ->phrase_id->toBe($en)->group->toBe('cart')->key->toBe('title');
});
it('keeps an empty string', function () {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $repo = app(SiteTextRepository::class);
    expect($repo->save($repo->all()->sole(), ['nl' => ''])->values['nl'])->toBe('');
});
```

- [ ] **Step 2: Run** — attendu : FAIL (méthode absente).
- [ ] **Step 3: Implement** `save()` dans une transaction.
- [ ] **Step 4: Run** — attendu : PASS.
- [ ] **Step 5: Commit** `feat: save site texts per language`

---

### Task 4: Recherche et filtres

**Files:**
- Create: `app/SiteTexts/SiteTextFilter.php`
- Test: `tests/Unit/SiteTexts/SiteTextFilterTest.php`

**Interfaces:**
- Consumes: `SiteText`, `SiteTextConfig::zones()`, `untranslatedExceptions()`.
- Produces:
  - `SiteTextFilter::apply(Collection $texts, string $search = '', ?string $zone = null, string $show = 'all', bool $technical = false): Collection` — `$show` ∈ `all`, `untranslated`, `recent`.
  - `SiteTextFilter::needsTranslation(SiteText $text): bool`.
  - `SiteTextFilter::matches(SiteText $text, string $search): array<string, bool>` — par champ (`label`, `fr`, `nl`, `en`), pour le surlignage.

Règles :
- recherche : `Str::contains($haystack, $search, ignoreCase: true)` sur le libellé, la clé complète et les trois valeurs (HTML retiré par `strip_tags` pour les textes longs) ;
- `untranslated` : une valeur `null` ou `''`, ou une valeur NL/EN identique au FR, sauf si le FR est numérique ou figure dans les exceptions ;
- `recent` : `updatedAt >= now()->subDays(30)` ;
- zones techniques exclues sauf si `$technical` ou si `$zone` est cette zone.

- [ ] **Step 1: Write the failing tests** — construire des `SiteText` à la main :

```php
it('searches the three languages without case', fn () =>
    expect(filter([text(nl: 'Facturatieadres')], search: 'facturatie'))->toHaveCount(1));
it('searches the readable name', fn () =>
    expect(filter([text(label: 'Adresse de facturation')], search: 'ADRESSE'))->toHaveCount(1));
it('lists a text empty in dutch as untranslated', fn () =>
    expect(filter([text(fr: 'Panier', nl: '', en: 'Cart')], show: 'untranslated'))->toHaveCount(1));
it('lists a dutch text identical to french as untranslated', fn () =>
    expect(filter([text(fr: 'Panier', nl: 'Panier', en: 'Cart')], show: 'untranslated'))->toHaveCount(1));
it('ignores the configured exceptions', function () {
    config(['site-texts.untranslated_exceptions' => ['Best Seller']]);
    expect(filter([text(fr: 'Best Seller', nl: 'Best Seller', en: 'Best Seller')], show: 'untranslated'))->toBeEmpty();
});
it('keeps texts changed in the last 30 days', fn () =>
    expect(filter([text(updatedAt: now()->subDays(31)), text(updatedAt: now()->subDays(2))], show: 'recent'))->toHaveCount(1));
it('hides technical zones by default', fn () =>
    expect(filter([text(zone: 'validation')]))->toBeEmpty());
```

(`text()` et `filter()` : petits helpers locaux au fichier de test, valeurs par défaut non vides et différentes.)

- [ ] **Step 2: Run** — FAIL. **Step 3: Implement.** **Step 4: Run** — PASS.
- [ ] **Step 5: Commit** `feat: search and filter site texts`

---

### Task 5: Enregistrer et publier

**Files:**
- Create: `app/SiteTexts/SaveSiteTextAction.php`
- Test: `tests/Feature/SiteTexts/SaveSiteTextActionTest.php`

**Interfaces:**
- Consumes: `SiteTextRepository::save()`, `App\Actions\Translations\ExportTranslationsAction::handle()`.
- Produces: `SaveSiteTextAction::handle(SiteText $text, array<string, ?string> $values): SaveResult` avec `final readonly class SaveResult { public SiteText $text; public bool $published; public ?string $error; }` (dans le même fichier ou `app/SiteTexts/SaveResult.php`).

L'export est appelé après l'enregistrement. Une exception de l'export est attrapée et journalisée (`report($e)`) : la valeur reste en base, `published = false`, `error` = message.

- [ ] **Step 1: Write the failing tests** — `useLangPath()` vers un dossier temporaire (même setup que `ExportTranslationsCommandTest`) :

```php
it('publishes the new value to the lang files', function () {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $text = app(SiteTextRepository::class)->all()->sole();

    $result = app(SaveSiteTextAction::class)->handle($text, ['fr' => 'Panier']);

    expect($result->published)->toBeTrue()
        ->and(require $this->langPath . '/fr/cart.php')->toBe(['title' => 'Panier']);
});
it('keeps the value when the export fails', function () {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    $this->mock(ExportTranslationsAction::class)->shouldReceive('handle')->andThrow(new RuntimeException('disk full'));

    $result = app(SaveSiteTextAction::class)->handle(app(SiteTextRepository::class)->all()->sole(), ['fr' => 'Panier']);

    expect($result->published)->toBeFalse()->and($result->error)->toBe('disk full')
        ->and($result->text->values['fr'])->toBe('Panier');
});
```

- [ ] **Step 2–4:** FAIL, implémenter, PASS.
- [ ] **Step 5: Commit** `feat: publish site texts right after saving`

---

### Task 6: La page « Textes du site » (textes courts)

**Files:**
- Create: `app/Filament/Pages/SiteTexts.php`, `resources/views/filament/pages/site-texts.blade.php`
- Test: `tests/Feature/SiteTexts/SiteTextsPageTest.php`, et ajouter la page au dataset « custom admin pages » de `tests/Feature/Admin/AdminPagesRenderTest.php`

**Interfaces:**
- Consumes: `SiteTextRepository::all()/find()`, `SiteTextFilter::apply()/matches()/needsTranslation()`, `SiteTextConfig::zones()`, `SaveSiteTextAction::handle()`.
- Produces (état Livewire public, utilisé par la tâche 7) :
  - `public string $search = ''`, `public ?string $zone = null`, `public string $show = 'all'`, `public bool $technical = false` ;
  - `public array $openZones = []` (ids de zone) ;
  - `public array $drafts = []` — `[SiteText::id() => ['fr' => ?string, 'nl' => ?string, 'en' => ?string]]`, rempli à l'ouverture d'une zone ;
  - `public function toggleZone(string $zone): void` ;
  - `public function saveRow(string $id): void`.

Page :
- `protected static ?string $navigationGroup = 'Contenu'`, `$navigationLabel = 'Textes du site'`, `$title = 'Textes du site'`, `$slug = 'textes-du-site'`, icône `heroicon-o-language` ;
- `public static function canAccess(): bool` → admin seulement (couvre la navigation et le 403) ;
- vue : barre (recherche `wire:model.live.debounce.400ms`, sélecteur de zone, radios Tous / À traduire / Modifiés récemment, case « Afficher les textes techniques ») ; puis une section repliable par zone avec le nombre de textes filtrés ; une zone fermée ne rend aucun champ ;
- quand `$search` n'est pas vide, les zones qui contiennent un résultat sont ouvertes (`updatedSearch()`) ;
- ligne : libellé (clé complète en `title=` au survol, petite police) ; trois `<input>` avec `<label>` « {libellé} — français / néerlandais / anglais », `wire:model="drafts.{id}.fr"` ; bordure orange si la valeur est vide ; anneau de surlignage sur les champs renvoyés par `matches()` ; bouton « Enregistrer » ;
- `saveRow()` : relit le texte avec `find()`, appelle `SaveSiteTextAction`, puis `Notification::make()->title('Texte enregistré et publié')->success()`, ou `->warning()` avec « Texte enregistré, mais pas encore publié : {error}. Il le sera au prochain enregistrement ou au prochain déploiement. ».

- [ ] **Step 1: Write the failing tests**

```php
it('is reserved to admins', function (string $role, int $status) {
    $role === 'admin' ? loginAsAdmin() : ($role === 'agent' ? loginAsAgent() : loginAsUser());
    $this->get(SiteTexts::getUrl())->assertStatus($status);
})->with(['admin' => ['admin', 200], 'agent' => ['agent', 403], 'customer' => ['customer', 403]]);

it('shows the zones closed with their count', function () {
    loginAsAdmin();
    ltuPhrase('en', 'order', 'location.invoice', 'Invoice address');
    livewire(SiteTexts::class)->assertSee('PDF bon de commande (1)')->assertDontSee('Invoice address');
});

it('saves a row whose key contains dots and publishes it', function () {
    loginAsAdmin();
    ltuPhrase('en', 'order', 'location.invoice', 'Invoice address');
    $id = app(SiteTextRepository::class)->all()->sole()->id();

    livewire(SiteTexts::class)
        ->call('toggleZone', 'order')
        ->set("drafts.{$id}.nl", 'Factuuradres')
        ->call('saveRow', $id)
        ->assertNotified('Texte enregistré et publié');

    expect(app(SiteTextRepository::class)->find($id)->values['nl'])->toBe('Factuuradres');
});

it('opens the zones that match the search', function () {
    loginAsAdmin();
    ltuPhrase('nl', 'order', 'location.invoice', 'Factuuradres');
    livewire(SiteTexts::class)->set('search', 'factuur')->assertSet('openZones', ['order']);
});
```

(Ces tests utilisent un dossier `lang` temporaire comme la tâche 5.)

- [ ] **Step 2: Run** — FAIL. **Step 3: Implement** la page et la vue (composants `x-filament::section` collapsible, `x-filament::input.wrapper`). **Step 4: Run** — PASS, plus `tests/Feature/Admin`.
- [ ] **Step 5: Vérifier dans le navigateur** (`http://cetronic.test/admin/textes-du-site`, session admin) : ouvrir « Panier et commande », modifier un texte NL, enregistrer, puis `php artisan tinker` / page du site en NL pour voir le nouveau texte ; console sans erreur. Remettre la valeur d'origine ensuite.
- [ ] **Step 6: Commit** `feat: add the site texts admin page`

---

### Task 7: Textes longs avec éditeur riche

**Files:**
- Create: `app/SiteTexts/RichTextCleaner.php`
- Modify: `app/Filament/Pages/SiteTexts.php`, sa vue, `composer.json` (`symfony/html-sanitizer` en `require`, même contrainte que la version déjà installée)
- Test: `tests/Unit/SiteTexts/RichTextCleanerTest.php`, `tests/Feature/SiteTexts/SiteTextsPageTest.php`

**Interfaces:**
- Consumes: état de la page (tâche 6), `SaveSiteTextAction`.
- Produces:
  - `RichTextCleaner::clean(string $html): string` — `HtmlSanitizer` configuré avec `allowElement()` pour `p`, `br`, `strong`, `em`, `ul`, `ol`, `li` et `a` avec l'attribut `href` ; liens limités à `http`, `https`, `mailto`.
  - `RichTextCleaner::toEditor(string $html): string` — si le texte ne contient pas de `<p>`, découpe sur `(<br\s*/?>\s*){2,}` et enveloppe chaque morceau dans `<p>…</p>` ; sinon le rend tel quel.
  - Action Filament `editLongText` sur la page (`Filament\Actions\Action`, argument `id`), formulaire `Tabs` FR / NL / EN, un `RichEditor` par langue avec `->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList'])`, bouton de validation « Enregistrer et publier ».

Une ligne `long` n'affiche pas de champs : un aperçu en texte (`Str::limit(strip_tags(...), 120)` par langue) et un bouton « Modifier le texte » qui monte l'action. À l'enregistrement : `clean()` sur chaque langue, puis `SaveSiteTextAction`.

- [ ] **Step 1: Write the failing tests**

```php
it('keeps only the toolbar tags', fn () => expect(app(RichTextCleaner::class)->clean(
    '<h1>T</h1><p style="color:red">A <strong>b</strong><script>x()</script> <a href="https://x.be" onclick="y()">l</a></p>'
))->toBe('T<p>A <strong>b</strong> <a href="https://x.be">l</a></p>'));

it('turns double line breaks into paragraphs', fn () =>
    expect(app(RichTextCleaner::class)->toEditor('Bonjour<br><br>Merci'))->toBe('<p>Bonjour</p><p>Merci</p>'));

it('edits a long text in the rich editor, not in a plain field', function () {
    loginAsAdmin();
    ltuPhrase('en', 'legal', 'cgv.claims', '<p>Any claim</p>');
    $id = app(SiteTextRepository::class)->all()->sole()->id();

    livewire(SiteTexts::class)
        ->call('toggleZone', 'legal')
        ->assertDontSeeHtml("drafts.{$id}.en")
        ->callAction('editLongText', ['fr' => '<p><strong>Toute</strong> réclamation</p>', 'nl' => '<p>Elke klacht</p>', 'en' => '<p>Any claim</p>'], arguments: ['id' => $id])
        ->assertHasNoActionErrors();

    expect(app(SiteTextRepository::class)->find($id)->values['fr'])->toBe('<p><strong>Toute</strong> réclamation</p>');
});
```

(Adapter l'attendu exact de `clean()` à la sortie réelle de `HtmlSanitizer` après le premier passage si seul l'ordre des attributs ou les espaces diffèrent ; la règle à tenir est : ni `h1`, ni `style`, ni `script`, ni `onclick`.)

- [ ] **Step 2–4:** FAIL, implémenter, PASS.
- [ ] **Step 5: Vérifier dans le navigateur** : ouvrir un texte de « Nous connaître », mettre un mot en gras et ajouter une liste, enregistrer, vérifier la page publique dans les trois langues. Remettre la valeur d'origine.
- [ ] **Step 6: Commit** `feat: edit long site texts with a rich editor`

---

### Task 8: Noms lisibles pré-remplis

**Files:**
- Modify: `config/site-texts.php`
- Test: `tests/Unit/SiteTexts/SiteTextConfigTest.php`

Remplir `labels` (et `long` si un texte long n'a pas de balise) pour les zones à fort usage : pied de page (`home.footer.*`), PDF bon de commande (`order.*`), e-mails (`emails.*`), promotions (`listing.*` liés aux promos), bandeaux (`item.*`), menu (`nav.*`). Source des clés : `php artisan tinker` → `DB::table('ltu_phrases')->where('translation_id', 1)->whereIn('group', [...])->get(['group', 'key', 'value'])` ; le nom lisible est en français, court, tel que le client le voit (souvent la valeur FR elle-même, raccourcie).

- [ ] **Step 1: Write the failing test** — toute clé configurée existe :

```php
it('only names keys that exist', function () {
    $keys = collect(config('site-texts.labels'))->keys();
    $known = DB::table('ltu_phrases')->where('translation_id', 1)->get()->map(fn ($p) => "{$p->group}.{$p->key}");
    expect($keys->diff($known))->toBeEmpty();
})->skip(fn () => DB::table('ltu_phrases')->doesntExist(), 'needs the local translations database');
```

Ce test tourne contre la base locale (`DB_CONNECTION` de `.env`) : le lancer avec `./vendor/bin/pest --filter="only names keys" --configuration=phpunit.xml` après avoir vérifié qu'il se saute bien en SQLite vide ; sinon, le remplacer par un script `php artisan tinker` exécuté à la main et noté dans le commit.

- [ ] **Step 2: Remplir** la config. **Step 3: Vérifier** que chaque zone pré-remplie s'affiche avec ses noms dans le navigateur.
- [ ] **Step 4: Commit** `feat: name the most edited site texts`

---

### Task 9: Créer de nouveaux textes (`translations:import-missing`)

`/translations` a disparu avec le paquet : il faut un moyen d'ajouter les nouvelles clés en base. Sans ça, une clé ajoutée par le développeur dans `lang/fr/cart.php` serait **effacée** au déploiement, car l'export réécrit `cart.php` depuis la base.

**Files:**
- Create: `app/Console/Commands/ImportMissingTranslationsCommand.php`
- Test: `tests/Feature/Translations/ImportMissingTranslationsCommandTest.php`

**Interfaces:**
- Produces: commande `translations:import-missing` — pour chaque langue présente dans `ltu_languages` et chaque fichier `lang/{code}/*.php`, aplatit le tableau (`Arr::dot`) et insère les clés absentes (langue + fichier + clé). Crée le fichier `ltu_translation_files` au besoin, lie `fr`/`nl` à la ligne `en` (`phrase_id`), `group` = nom du fichier. **N'écrase jamais** une valeur existante. Affiche le nombre de clés ajoutées.

- [ ] **Step 1: Write the failing tests** (lang temporaire + `ltuPhrase`) :

```php
it('adds the keys missing in the database', function () {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    File::put($this->langPath . '/en/cart.php', "<?php return ['title' => 'Basket', 'new' => ['label' => 'New']];");

    $this->artisan('translations:import-missing')->assertSuccessful();

    expect(DB::table('ltu_phrases')->where('key', 'new.label')->value('value'))->toBe('New')
        ->and(DB::table('ltu_phrases')->where('key', 'title')->value('value'))->toBe('Cart');
});
it('survives an export afterwards', function () {
    ltuPhrase('en', 'cart', 'title', 'Cart');
    File::put($this->langPath . '/en/cart.php', "<?php return ['title' => 'Cart', 'new' => 'New'];");
    $this->artisan('translations:import-missing');
    $this->artisan('translations:export');
    expect(require $this->langPath . '/en/cart.php')->toBe(['title' => 'Cart', 'new' => 'New']);
});
```

- [ ] **Step 2–4:** FAIL, implémenter, PASS.
- [ ] **Step 5: Commit** `feat: import new translation keys from lang files`
- [ ] **Step 6: Signaler à l'utilisateur** la ligne à ajouter au script Forge, **avant** l'export : `$FORGE_PHP artisan translations:import-missing`. Ne pas la considérer comme faite tant qu'il ne l'a pas confirmée.

---

### Task 10: Documentation et vérification finale

**Files:**
- Modify: `docs/specs/2026-11-12-textes-du-site.md` (§ 1, § 2 hors périmètre, § 4.1 : `/translations` n'existe plus ; création des textes via `lang/` + `translations:import-missing`), `PLAN_TRAVAIL.md` § 2.7 (étapes 1 à 3 faites).

- [ ] **Step 1:** Mettre à jour les deux documents.
- [ ] **Step 2:** `./vendor/bin/pest` — attendu : tout vert. `./vendor/bin/phpstan analyse --memory-limit=2G` — attendu : `[OK] No errors`.
- [ ] **Step 3:** Pint sur les fichiers créés ; `git diff main --stat` puis `git diff main | grep -E '^\+.*mb_(l|r)?trim\('` — attendu : aucune ligne.
- [ ] **Step 4:** Critères d'acceptation 2, 3, 4 et 7 de la spec vérifiés dans le navigateur (1, 5 et 6 relèvent du second plan).
- [ ] **Step 5: Commit** `docs: update site texts spec and work plan`
