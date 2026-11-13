# Admin "Boutique" look & feel (Part 1) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give the Filament 3 admin the validated "Boutique" look (cream, black, serif titles, Cetronic logo), a French UI and menu, and dense lists/forms for Produits, Commandes, Clients and Paniers.

**Architecture:** A Filament custom theme (`resources/css/filament/admin/theme.css`, Filament's Tailwind preset) compiled by the existing Vite build and wired with `->viteTheme()`; panel settings (logo, fonts, colours) in `AdminPanelProvider`; a panel middleware that runs the admin in French; per-resource changes (labels, list tabs, column visibility, form layout) in the four main resources.

**Tech Stack:** Laravel 10, Filament 3.3, Livewire 3, Tailwind 3.4, Vite 3, Pest.

**Spec:** `docs/superpowers/specs/2026-11-13-admin-redesign-design.md` (Part 1 only; Part 2 dashboard gets its own plan)

## Global Constraints

- Prod runs PHP 8.3: no 8.4 syntax; after Pint, `git diff | grep -E 'mb_(l|r)?trim\('` must be empty (use `Str::of($x)->trim()->value()`).
- No database change, no new business feature, no dark mode, no change to the public site's look.
- Every existing filter, action, bulk action, page and form field stays.
- An e-mail sent to a customer must not change language because the admin runs in French.
- Menu entries an agent cannot open stay hidden, as today.
- Visual tokens: sidebar `#f6efe6`; surfaces/cards `#faf6f0`; borders `#eee5da` / `#e3dbd0`; text `#111`; muted `#8a8178`; primary `#111`; success `#2f6b45`; danger `#9b3b2f`. Page titles serif, body sans-serif. Logo `public/logo.svg`.
- Commits GPG-signed, Conventional Commits in English, on branch `feat/admin-boutique-redesign`. Never push.
- Done = `php artisan test --compact` green, `vendor/bin/phpstan analyse` OK.

## Review Focus

1. Tailwind 3.1 → 3.4 changes the public site's compiled CSS → Task 1 diffs the built `app.css`/`front.css` before and after.
2. Admin in French makes a customer e-mail (account activation, welcome) go out in French → Task 3 pins it with a test.
3. An agent logging in sees a menu entry or group they could not open before → Task 4 tests the agent's navigation.
4. A list loses an existing filter, action or bulk action while being reworked → Tasks 5, 7, 8, 9 assert they still exist.
5. A product without any picture, or the 5 800-product list, breaks or slows the thumbnail column → Task 5 tests a product with no media and eager-loads media.

## Decisions taken in this plan (rulings for the executor)

- Fonts: body **Inter**, page titles **Playfair Display**, both from Google Fonts (`->font('Inter')` + `@import` in the theme for Playfair).
- Colours: `primary` = `Color::hex('#111111')`, `gray` = `Color::Stone` (warm greys matching cream); cream/surface/border tokens applied in `theme.css`.
- French UI: panel middleware `App\Http\Middleware\SetAdminLocale` calls `app()->setLocale('fr')`. Customer mails are already safe: `User` implements `HasLocalePreference`, so `Mail::to($user)` uses the customer's language; Task 3 proves it.
- Product "Caractéristiques": metas are a relation manager, not form fields. The form gets tabs **Général · Prix · Photos · Ventes** and the metas relation manager is titled **Caractéristiques** (shown under the form, as today).
- List quick filters = Filament list tabs (`getTabs()` on the List page).

---

### Task 1: Theme tooling

**Files:**
- Modify: `package.json` (tailwindcss `^3.4`, add `@tailwindcss/typography`), `vite.config.js` (add input `resources/css/filament/admin/theme.css`), `app/Providers/Filament/AdminPanelProvider.php` (`->viteTheme('resources/css/filament/admin/theme.css')`)
- Create: `resources/css/filament/admin/theme.css`, `resources/css/filament/admin/tailwind.config.js` (Filament's `make:filament-theme admin` output, content globs incl. `app/Filament/**/*.php`, `resources/views/filament/**/*.blade.php`, `resources/views/livewire/admin/**/*.blade.php`, `vendor/filament/**/*.blade.php`)
- Test: `tests/Feature/Admin/AdminThemeTest.php`

**Interfaces:**
- Produces: `resources/css/filament/admin/theme.css` — every later visual task adds its CSS here.

- [ ] **Step 1: Build the current assets and keep them for comparison**

Run: `npm run build && mkdir -p /tmp/cetronic-css-before && cp public/build/assets/*.css /tmp/cetronic-css-before/`
Expected: build OK, `app.*.css` and `front.*.css` copied.

- [ ] **Step 2: Write the failing test**

```php
it('loads the admin theme', function (): void {
    expect(Filament::getPanel('admin')->getViteTheme())   // use the panel's accessor for the vite theme
        ->toContain('resources/css/filament/admin/theme.css');
});
```

- [ ] **Step 3: Run it** — `php artisan test --compact tests/Feature/Admin/AdminThemeTest.php` → FAIL.

- [ ] **Step 4: Scaffold the theme** (`php artisan make:filament-theme admin`, then upgrade `tailwindcss` to `^3.4` and add `@tailwindcss/typography` as it asks; `npm install`), wire `->viteTheme()`, add the vite input.

- [ ] **Step 5: Run the test** → PASS. Then `npm run build` → OK.

- [ ] **Step 6: Prove the public CSS is unchanged**

Run: `for f in app front; do diff <(npx prettier --parser css /tmp/cetronic-css-before/$f.*.css) <(npx prettier --parser css public/build/assets/$f.*.css) | head -40; done`
Expected: no difference. If Tailwind 3.4 changed something, list each change in the ledger as a ruling (harmless reorder vs visible change); a visible change on the public site stops the task.

- [ ] **Step 7: Commit** — `feat: add Filament admin theme build`

### Task 2: Boutique visual identity

**Files:**
- Modify: `app/Providers/Filament/AdminPanelProvider.php` (`brandLogo(asset('logo.svg'))`, `brandLogoHeight('2rem')`, `favicon`, `->font('Inter')`, colours per Decisions), `resources/css/filament/admin/theme.css`
- Test: `tests/Feature/Admin/AdminThemeTest.php`

**Interfaces:**
- Consumes: Task 1 `theme.css`.

- [ ] **Step 1: Failing tests**

```php
it('shows the Cetronic logo on the login page', function (): void {
    $this->get('/admin/login')->assertOk()->assertSee('logo.svg', false);
});

it('uses black as primary colour and Inter as font', function (): void {
    $panel = Filament::getPanel('admin');
    expect($panel->getFontFamily())->toBe('Inter')
        ->and(collect($panel->getColors()['primary'])->get(950))->not->toBeNull();
    // primary must be the #111 palette, not Amber
    expect($panel->getColors()['primary'])->not->toBe(Color::Amber);
});
```

- [ ] **Step 2: Run** → FAIL.
- [ ] **Step 3: Implement** panel settings; in `theme.css`: Playfair Display `@import`, serif on `.fi-header-heading`, `.fi-simple-header-heading`, section headings; sidebar background `#f6efe6`; cards/sections on `#faf6f0` with `#eee5da` borders; active nav item black with white text; table header row with a 1px `#111` top border and uppercase 10px muted headers; table cells `padding-block: .375rem` and `text-sm` (rows about half today's height); form fields gap tightened.
- [ ] **Step 4: Run** tests → PASS; `npm run build`; open `/admin/items` locally (`http://cetronic.test/admin`) and compare with the validated mockup `.superpowers/brainstorm/24463-1794539777/content/listes-formulaires.html`; save a screenshot.
- [ ] **Step 5: Commit** — `feat: apply the Boutique look to the admin`

### Task 3: Admin in French, customer mails untouched

**Files:**
- Create: `app/Http/Middleware/SetAdminLocale.php` (`handle()` sets `app()->setLocale('fr')`)
- Modify: `app/Providers/Filament/AdminPanelProvider.php` (add it to `->middleware([...])` and to `->authMiddleware` is not needed)
- Test: `tests/Feature/Admin/AdminLocaleTest.php`

- [ ] **Step 1: Failing tests**

```php
it('runs the admin in French', function (): void {
    loginAsAdmin();
    $this->get(ItemResource::getUrl())->assertOk()->assertSee('Rechercher');
});

it('keeps the customer language for the activation e-mail sent from the admin', function (): void {
    Mail::fake();
    loginAsAdmin();
    $customer = User::factory()->create(['role' => 'customer', 'is_actif' => false, 'language' => 'nl']);

    livewire(EditUser::class, ['record' => $customer->getRouteKey()])
        ->fillForm(['is_actif' => true])->call('save')->assertHasNoFormErrors();

    Mail::assertSent(ActifUser::class, fn(ActifUser $mail) => 'nl' === $mail->locale);
});
```

- [ ] **Step 2: Run** → first FAIL ("Search"), second may already pass (that is the proof the anti-goal holds; keep it as a guard).
- [ ] **Step 3: Implement** the middleware and wire it.
- [ ] **Step 4: Run** both → PASS; run `tests/Feature/Admin` → green (fix English assertions in existing admin tests only where Filament's own UI strings changed, ledger each).
- [ ] **Step 5: Commit** — `feat: run the admin panel in French`

### Task 4: French menu, reordered, distinct icons

**Files:**
- Modify: every `app/Filament/Resources/*Resource.php` and `app/Filament/Pages/*.php` (`$navigationGroup`, `$navigationLabel`, `$modelLabel`, `$pluralModelLabel`, `$navigationIcon`, `$navigationSort`), `AdminPanelProvider` (`->navigationGroups([...])` in order)
- Test: `tests/Feature/Admin/AdminNavigationTest.php`

**Interfaces:**
- Produces: group names `Ventes`, `Catalogue`, `Clients`, `Contenu`, `Imports` (Part 2 adds nothing to them).

Expected menu (spec, in order):

| Group | Entries |
|---|---|
| — | Tableau de bord |
| Ventes | Commandes, Paniers, Statistiques ventes |
| Catalogue | Produits, Marques, Catégories, Pubs, Traductions des métas, Journal de publication |
| Clients | Clients, Types de client, Import remises marque |
| Contenu | Textes du site, Messages, Catalogues PDF |
| Imports | Import CSV, Import photos |

- [ ] **Step 1: Failing tests**

```php
function navigationFor(): array
{
    return collect(Filament::getNavigation())
        ->mapWithKeys(fn($group) => [(string) $group->getLabel() => collect($group->getItems())->map(fn($i) => $i->getLabel())->values()->all()])
        ->all();
}

it('shows the French menu in order to an admin', function (): void {
    loginAsAdmin();
    $this->get('/admin');   // boots the panel navigation
    expect(navigationFor())->toBe([
        '' => ['Tableau de bord'],
        'Ventes' => ['Commandes', 'Paniers', 'Statistiques ventes'],
        'Catalogue' => ['Produits', 'Marques', 'Catégories', 'Pubs', 'Traductions des métas', 'Journal de publication'],
        'Clients' => ['Clients', 'Types de client', 'Import remises marque'],
        'Contenu' => ['Textes du site', 'Messages', 'Catalogues PDF'],
        'Imports' => ['Import CSV', 'Import photos'],
    ]);
});

it('gives every menu entry its own icon', function (): void { /* collect icons of all items: count === unique count */ });

it('shows an agent no more entries than before', function (): void {
    loginAsAgent();
    // capture BEFORE changing anything: run this test on the current code first and
    // paste the agent's entry count (and labels translated) as the expectation
});
```

- [ ] **Step 2: Run** on the current code; record the agent's current entries; then the first test FAILs.
- [ ] **Step 3: Implement** groups/labels/icons/sorts (Heroicons outline, e.g. Commandes `shopping-bag`, Paniers `shopping-cart`, Statistiques `chart-bar`, Produits `cube`, Marques `tag`, Catégories `squares-2x2`, Pubs `megaphone`, Traductions des métas `language`, Journal `clipboard-document-list`, Clients `users`, Types de client `user-group`, Import remises `receipt-percent`, Textes du site `pencil-square`, Messages `chat-bubble-bottom-center-text`, Catalogues PDF `document-text`, Import CSV `arrow-up-tray`, Import photos `photo`).
- [ ] **Step 4: Run** → PASS; `AdminPagesRenderTest` green.
- [ ] **Step 5: Commit** — `feat: reorganise the admin menu in French`

### Task 5: Products list

**Files:**
- Modify: `app/Filament/Resources/ItemResource.php` (table), `app/Filament/Resources/ItemResource/Pages/ListItems.php` (`getTabs()`; remove unused `Filament\Tables\Filters\Layout` import)
- Test: `tests/Feature/Admin/ItemListTest.php`

Columns visible by default, in order, French headers: photo (thumbnail, `SpatieMediaLibraryImageColumn`, conversion `thumb`, first picture only, square 2rem), Référence, Marque, Catégorie, Prix B2B, Promo (empty when 0), Statut (En ligne / Hors ligne), Vendus (`orders_count`). Toggleable and hidden by default: Produit maître, Best-seller, Quantité multiple, Photos (`media_count`), Créé le, Modifié le. Tabs: `tous` Tous · `en-ligne` En ligne (`is_published`) · `hors-ligne` Hors ligne · `promo` En promo (`price_promo > 0`) · `sans-photo` Sans photo (`doesntHave('media')`), each with its count badge.

- [ ] **Step 1: Failing tests**

```php
it('filters products with the quick tabs', function (string $tab, array $visible, array $hidden): void { ... })
    ->with([...]); // build 4 items: published+promo+photo, published no photo, unpublished, ...; assertCanSeeTableRecords / assertCanNotSeeTableRecords after ->set('activeTab', $tab)

it('shows only the useful columns by default', function (): void {
    loginAsAdmin();
    livewire(ListItems::class)
        ->assertTableColumnVisible('reference')->assertTableColumnVisible('price_promo')
        ->assertTableColumnHidden('created_at')->assertTableColumnHidden('master.reference');
});

it('keeps every filter, action and bulk action', function (): void {
    loginAsAdmin();
    livewire(ListItems::class)
        ->assertTableFilterExists('brand')->assertTableFilterExists('category')
        ->assertTableFilterExists('is_published')->assertTableFilterExists('is_new')->assertTableFilterExists('is_best_seller')
        ->assertTableActionExists('Duplicate')
        ->assertTableBulkActionExists('published')->assertTableBulkActionExists('unpublished');
});

it('lists a product that has no picture', function (): void {
    loginAsAdmin();
    $item = Item::factory()->create();
    livewire(ListItems::class)->assertOk()->assertCanSeeTableRecords([$item]);
});
```

- [ ] **Step 2: Run** → FAIL.
- [ ] **Step 3: Implement**; eager-load `media` in the table query (`modifyQueryUsing(fn($q) => $q->with('media'))`) so the thumbnail adds no query per row.
- [ ] **Step 4: Run** → PASS.
- [ ] **Step 5: Commit** — `feat: dense French products list with quick tabs`

### Task 6: Product form

**Files:**
- Modify: `app/Filament/Resources/ItemResource.php` (form), `app/Filament/Resources/ItemResource/RelationManagers/MetasRelationManager.php` (`$title = 'Caractéristiques'`)
- Test: `tests/Feature/Admin/ItemFormTest.php`

Layout: a 3-column grid. Left, span 2: tabs **Général** (Marque, Catégorie, Référence, Produit maître, Quantité multiple, Groupe catalogue, Description full width; three columns), **Prix** (the nine price fields, French labels, three columns), **Photos** (the existing upload, unchanged settings), **Ventes** (existing view). Right, span 1: section **Publication** with the three toggles (En ligne `is_published`, Nouveauté `is_new`, Best-seller `is_best_seller`) and a section **En bref** with read-only placeholders: Prix B2B, Vendus, Photos, Modifié le (hidden on create). Every existing field and its rules stay.

- [ ] **Step 1: Failing tests**

```php
it('shows the product form tabs and the publication panel', function (): void {
    loginAsAdmin();
    $item = Item::factory()->create();
    livewire(EditItem::class, ['record' => $item->getRouteKey()])
        ->assertSee(['Général', 'Prix', 'Photos', 'Ventes', 'Publication', 'En bref', 'Caractéristiques']);
});

it('still saves every field', function (): void {
    loginAsAdmin();
    $item = Item::factory()->create(['is_published' => false]);
    livewire(EditItem::class, ['record' => $item->getRouteKey()])
        ->fillForm(['price_b2b' => 1240, 'is_published' => true, 'multiple_quantity' => 6])
        ->call('save')->assertHasNoFormErrors();
    expect($item->fresh())->is_published->toBeTrue()->multiple_quantity->toBe(6);
});

it('creates a product without the summary panel failing', function (): void { /* CreateItem renders OK */ });
```

- [ ] **Step 2: Run** → FAIL. **Step 3: Implement.** **Step 4: Run** → PASS (plus `RelationManagerFormsTest`).
- [ ] **Step 5: Commit** — `feat: regroup the product form`

### Task 7: Orders list and form

**Files:** `app/Filament/Resources/OrderResource.php`, `.../OrderResource/Pages/ListOrders.php` · Test: `tests/Feature/Admin/OrderListTest.php`

French headers (Date, Référence, Client, Statut, Total HT, Articles); statut shown in French (Ouverte, En préparation, Expédiée). Tabs: Toutes · Ce mois (`created_at` in current month) · Ouvertes · En préparation · Expédiées. Form labels French (Référence, Statut, Suivi: Numéro de suivi, Lien de suivi). Filters `open`, `in preparation`, `shipped`, action `exportPDF` and bulk `shippend` stay.

- [ ] Step 1: failing tests (tab filtering with one order per status + one last month; exists-asserts for the filters/action/bulk action). Step 2: run → FAIL. Step 3: implement. Step 4: run → PASS. Step 5: commit `feat: French orders list with quick tabs`.

### Task 8: Customers list and form

**Files:** `app/Filament/Resources/UserResource.php`, `.../UserResource/Pages/ListUsers.php` · Test: `tests/Feature/Admin/UserListTest.php`

French headers (Réf. externe, Nom, E-mail, Type de compte, Type de client, Actif, Commandes; Créé le, Modifié le, Dernière connexion toggleable hidden by default). Tabs: Tous · Clients · Agents · Administrateurs · À activer (`role = customer` and `is_actif = false`). Form sections in French (Informations, Livraison, Activation, Remises par marque) with French labels. All 14 filters, actions `stats`, `Login`, `Print` and bulk `resetPassword` stay.

- [ ] Step 1: failing tests (tabs incl. "À activer"; exists-asserts for every filter/action/bulk action listed above). Steps 2–4 as above. Step 5: commit `feat: French customers list with quick tabs`.

### Task 9: Carts list and form

**Files:** `app/Filament/Resources/CartResource.php`, `.../CartResource/Pages/ListCarts.php` · Test: `tests/Feature/Admin/CartListTest.php`

French headers (Client, Statut: Ouvert / Vendu, Créé le, Modifié le). Tabs: Tous · Ouverts (`open`) · Vendus (`sold`). Form labels French (Client, Adresse de livraison, Adresse de facturation). Filters `open`, `close` stay.

- [ ] Step 1: failing tests. Steps 2–4. Step 5: commit `feat: French carts list with quick tabs`.

### Task 10: Final check

- [ ] `vendor/bin/pint` on changed PHP files, then the `mb_trim` grep (Global Constraints) → empty.
- [ ] `php artisan test --compact` → all green; `vendor/bin/phpstan analyse --memory-limit=1G` → OK.
- [ ] `npm run build` → OK; repeat Task 1 Step 6 diff → no public CSS change.
- [ ] Screenshots of `/admin/items`, `/admin/items/{id}/edit`, `/admin/orders`, `/admin/users` and the login page on `http://cetronic.test`, compared with the validated mockups; commit nothing for screenshots.
