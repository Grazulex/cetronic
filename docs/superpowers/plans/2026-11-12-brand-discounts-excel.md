# Export / import Excel des remises par marque — Plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Depuis l'admin, exporter en Excel les clients actifs avec leurs remises `user_brands` pour une marque choisie (une série de colonnes par catégorie de la marque), puis réimporter le fichier modifié pour mettre la base à jour.

**Architecture:** Une classe `BrandDiscountColumns` définit le format du fichier (en-têtes ↔ champs) et sert aux deux sens. L'export (`BrandDiscountsExport`) est déclenché par une action Filament sur la liste des clients. L'import passe par un service `BrandDiscountImporter` en deux temps (`analyse()` sans écriture → `apply()` transactionnel, sans observer), exposé dans une page Filament `ImportBrandDiscounts`.

**Tech Stack:** Laravel 10.50, Filament 2.17, maatwebsite/excel 3.1, Pest 2, Larastan 2 (niveau 5). Tests SQLite en mémoire, `QUEUE_CONNECTION=sync`.

**Spec:** analyse validée en conversation le 2026-11-12, décisions reprises ci-dessous.

## Spec (décisions validées)

- Table concernée : `user_brands` (onglet « Brands » de la fiche client), **pas** `user_discounts`.
- Lignes exportées : clients `role = customer`, `is_actif = true`, non supprimés, triés par nom.
- Colonnes client : `Nom`, `Email`, `Référence externe`, `Franco`, `Frais de port`. Seuls **référence externe, franco, frais de port** sont mis à jour à l'import.
- Catégories d'une marque = catégories ayant au moins un article **publié** de cette marque (les catégories avec uniquement des articles non publiés n'apparaissent pas).
- Par catégorie, 6 colonnes, toutes modifiables : `Réduction %`, `Coefficient`, `Ajout €`, `Type de prix`, `Masquer promo`, `Exclu`.
- Règle visée = (`user_id`, `brand_id`, `category_id`) avec `category_meta_id IS NULL AND category_meta_value IS NULL`. Les règles « meta » et sans catégorie ne sont jamais lues ni modifiées.
- Cellule vide = aucun changement (pas de création si pas de règle). `0` = valeur mise à 0. L'import ne supprime jamais de règle.

## Global Constraints

- PHP compatible **8.3** en prod : ne pas laisser Pint réécrire `trim()` en `mb_trim()` (voir mémoire prod-php-83-pint).
- `declare(strict_types=1);`, classes `final`, Pint pour le formatage.
- Valeurs monétaires et pourcentages stockés en entiers ×100 via accesseurs Eloquent : toujours écrire via le modèle, jamais en SQL brut.
- Écriture des `UserBrand` à l'import avec `saveQuietly()` : `UserBrandObserver` lance un job par article publié de la catégorie (jusqu'à 1 440 articles).
- Accès réservé aux admins (`UserRoleEnum::ADMIN`), comme `UserResource::canViewAny()`.
- Libellés UI en français, code et commits en anglais (Conventional Commits), branche `feat/brand-discounts-excel`.

## Review Focus

1. **Décimales à la belge** : le client tape `10,5` (texte) ou Excel renvoie `10.499999999` → accepter la virgule, arrondir à 2 décimales ; un aller-retour sans modification ne doit rien écrire. (Task 3 : `it('treats comma decimals and float noise as unchanged')`)
2. **Colonnes déplacées ou ajoutées** par le client : lecture par en-tête, pas par position ; un en-tête inconnu est ignoré, et un en-tête de catégorie mal formé est signalé comme erreur. (Task 3 : `it('maps columns by heading not position')`)
3. **Saisie libre du type de prix et des oui/non** (`B2B`, ` b2b `, `Oui`, `1`, `x`) → normalisation (trim + minuscules) ; toute autre valeur produit une erreur ligne/colonne. (Task 3 : `it('normalises price type and booleans')`)
4. **Fichier ancien ou d'une autre marque** : `ID marque` différent de la marque choisie, ou catégorie qui n'appartient plus à la marque → erreur bloquante, rien n'est écrit. (Task 3 : `it('rejects a file whose brand or categories do not match')`)
5. **Lignes vides ou supprimées** dans le fichier : ignorées ; un client absent du fichier n'est pas modifié. (Task 3 : `it('skips blank rows')`)

---

### Task 1: Fiabiliser `user_brands` (index unique, arrondis, doublons)

Prérequis à l'import : aujourd'hui les tests SQLite ne peuvent pas contenir une règle catégorie et une règle meta pour le même trio (index 3 colonnes, alors que la prod a 4 colonnes), les mutateurs ×100 produisent du bruit flottant (`12.35 * 100 = 1234.9999…`), et la prod contient 121 groupes de doublons.

**Files:**
- Create: `database/migrations/2026_11_12_000001_align_user_brands_unique_index.php`
- Create: `app/Console/Commands/DedupeUserBrandsCommand.php`
- Modify: `app/Models/UserBrand.php` (setters `reduction`, `coefficient`, `additionPrice`), `app/Models/User.php` (setters `franco`, `shippingPrice`)
- Test: `tests/Feature/UserBrand/UserBrandIntegrityTest.php`

**Interfaces:**
- Produces: commande artisan `user-brands:dedupe {--dry-run}` ; setters qui stockent `(int) round($value * 100)`.

- [ ] **Step 1: Créer la branche** — `git switch -c feat/brand-discounts-excel` (ne pas embarquer `composer.json`/`composer.lock`/`PLAN_TRAVAIL.md` modifiés, hors sujet).
- [ ] **Step 2: Écrire les tests qui échouent**

```php
it('allows a category rule and a meta rule for the same user, brand and category', function (): void {
    // UserBrand catégorie (meta null) + UserBrand avec category_meta_id/value sur le même trio
    expect(UserBrand::count())->toBe(2);
});

it('stores rounded hundredths', function (): void {
    $rule->reduction = 12.35; $rule->addition_price = 0.29; $rule->saveQuietly();
    expect($rule->getRawOriginal('reduction'))->toBe(1235)
        ->and($rule->getRawOriginal('addition_price'))->toBe(29);
    // idem User::franco = 150.10 → 15010
});

it('deletes identical duplicates keeping the lowest id', ...);          // 3 lignes identiques → 1 reste, id min
it('leaves conflicting duplicates untouched and reports them', ...);    // réductions 10 vs 20 → 2 lignes restent, sortie contient user/brand/category
it('ignores meta rules when deduplicating', ...);                        // règle meta identique en valeurs → conservée
it('changes nothing with --dry-run', ...);
```

Les doublons de test s'insèrent via `DB::table('user_brands')->insert()` (SQLite tolère les doublons quand une colonne de l'index vaut `NULL`, comme MySQL).

- [ ] **Step 3: Lancer** `php artisan test --filter=UserBrandIntegrityTest` → FAIL (contrainte unique, valeurs 1234/28, commande inconnue).
- [ ] **Step 4: Migration** — dans `up()`, lire `Schema::getIndexes('user_brands')` ; si l'index `user_brands_user_id_brand_id_category_id_unique` porte exactement `[user_id, brand_id, category_id]`, créer d'abord `user_brands_rule_unique` sur `[user_id, brand_id, category_id, category_meta_value]`, **puis** supprimer l'ancien (MySQL refuse de supprimer un index qui sert une FK s'il n'en existe pas d'autre commençant par `user_id`). Sinon (cas prod, déjà 4 colonnes) : ne rien faire. `down()` : no-op commenté.
- [ ] **Step 5: Setters** — `set: fn($value) => (int) round((float) $value * 100)` pour les 3 champs `UserBrand` et les 2 champs `User`.
- [ ] **Step 6: Commande `DedupeUserBrandsCommand`** — groupes = `category_id IS NOT NULL AND category_meta_id IS NULL AND category_meta_value IS NULL`, groupés par trio avec `COUNT(*) > 1`. Groupe « identique » si `reduction, coefficient, addition_price, price_type, not_show_promo, is_excluded` sont identiques sur toutes les lignes → supprimer toutes sauf l'id minimal via `DB::table()->delete()` (pas d'observer). Groupes contradictoires : tableau `user_id | email | brand | category | ids` en sortie, aucune suppression. Afficher le résumé `X groupes fusionnés, Y lignes supprimées, Z conflits`.
- [ ] **Step 7: Lancer** les tests → PASS ; puis `php artisan test` complet → aucun test cassé.
- [ ] **Step 8: Commit** `feat(user-brands): align unique index, round hundredths and add dedupe command`

> Mise en prod (après merge, sur demande) : `php artisan user-brands:dedupe --dry-run`, vérifier, puis sans `--dry-run`. Le conflit restant (1 groupe) est à arbitrer à la main dans l'onglet Brands.

---

### Task 2: Format du fichier et export

**Files:**
- Create: `app/Exports/BrandDiscounts/BrandDiscountColumns.php`
- Create: `app/Exports/BrandDiscounts/BrandDiscountsExport.php` (`WithMultipleSheets`)
- Create: `app/Exports/BrandDiscounts/BrandDiscountsSheet.php` (`FromArray, WithHeadings, WithTitle, WithEvents, ShouldAutoSize`)
- Create: `app/Exports/BrandDiscounts/BrandDiscountsLegendSheet.php` (`FromArray, WithTitle`)
- Modify: `app/Filament/Resources/UserResource/Pages/ListUsers.php` (nouvelle action `exportBrandDiscounts`)
- Test: `tests/Feature/BrandDiscounts/BrandDiscountsExportTest.php`

**Interfaces:**
- Produces:
  - `BrandDiscountColumns::FIELDS` : `['reduction' => 'Réduction %', 'coefficient' => 'Coefficient', 'addition_price' => 'Ajout €', 'price_type' => 'Type de prix', 'not_show_promo' => 'Masquer promo', 'is_excluded' => 'Exclu']`
  - `BrandDiscountColumns::USER_FIELDS` : `['user_id' => 'ID client', 'brand_id' => 'ID marque', 'name' => 'Nom', 'email' => 'Email', 'external_reference' => 'Référence externe', 'franco' => 'Franco', 'shipping_price' => 'Frais de port']`
  - `BrandDiscountColumns::categoriesFor(Brand $brand): Collection<Category>` — catégories avec au moins un `Item` publié de la marque, triées par `name`.
  - `BrandDiscountColumns::customers(): Builder<User>` — `role = customer`, `is_actif = true`, triés par `name`.
  - `BrandDiscountColumns::heading(Category $category, string $field): string` → `"{$category->name} [{$category->id}] {FIELDS[$field]}"`
  - `BrandDiscountColumns::parse(string $heading): ?array{category_id: int, field: string}` — `null` si l'en-tête n'a pas la forme `… [id] <libellé connu>`.
  - `BrandDiscountColumns::formatBool(bool): string` (`oui`/`non`).
  - `new BrandDiscountsExport(Brand $brand)` ; nom de fichier `Remises_{slug marque}_{Y-m-d_His}.xlsx`.

- [ ] **Step 1: Écrire les tests qui échouent**

```php
it('lists only categories holding published items of the brand, by name', ...);
// Watches (publié) + Rings (publié) + Straps (non publié seulement) + catégorie d'une autre marque
// → ['Rings', 'Watches']

it('exports active customers only', ...);
// inactif, agent, admin, supprimé (soft delete) absents ; 2 clients actifs présents, triés par nom

it('builds headings with category ids', function (): void {
    expect($sheet->headings())->toBe([
        'ID client', 'ID marque', 'Nom', 'Email', 'Référence externe', 'Franco', 'Frais de port',
        'Rings [1] Réduction %', 'Rings [1] Coefficient', 'Rings [1] Ajout €', 'Rings [1] Type de prix', 'Rings [1] Masquer promo', 'Rings [1] Exclu',
        'Watches [2] Réduction %', /* … */
    ]);
});

it('fills rule values and leaves categories without rule empty', ...);
// règle Rings : reduction 12.5, coefficient 0, addition 1, b2b, not_show_promo true, is_excluded false
// → [12.5, 0, 1, 'b2b', 'oui', 'non'] ; Watches → 6 × null ; franco 150 / shipping 12.5 en unités lisibles

it('ignores meta rules and rules of other brands', ...);

it('parses its own headings back', function (): void {
    expect(BrandDiscountColumns::parse('Rings [1] Type de prix'))->toBe(['category_id' => 1, 'field' => 'price_type'])
        ->and(BrandDiscountColumns::parse('Commentaire'))->toBeNull();
});

it('downloads the file from the users list for admins', function (): void {
    Excel::fake();
    loginAsAdmin();
    livewire(ListUsers::class)->callPageAction('exportBrandDiscounts', ['brand_id' => $brand->id]);
    Excel::matchByRegex();
    Excel::assertDownloaded('/Remises_alpha_.*\.xlsx/');
});
```

- [ ] **Step 2: Lancer** `php artisan test --filter=BrandDiscountsExportTest` → FAIL (classes absentes).
- [ ] **Step 3: Implémenter `BrandDiscountColumns`** selon l'interface ci-dessus.
- [ ] **Step 4: Implémenter `BrandDiscountsSheet::array()`** — chargement des règles en **une** requête (`UserBrand::where('brand_id')->whereNull('category_meta_id')->whereNull('category_meta_value')->whereNotNull('category_id')->get()->keyBy(fn => "{$r->user_id}-{$r->category_id}")`), clients via `customers()->get()` (pas de N+1). Booléens via `formatBool`, `price_type` via `->value`.
- [ ] **Step 5: `registerEvents()` (AfterSheet)** — figer la ligne 1 et les colonnes A–D (`freezePane('E2')`), en-têtes en gras ; validations de données sur les lignes 2..N : liste `default,b2b,special_1,special_2,special_3,fix` sur les colonnes Type de prix, liste `oui,non` sur Masquer promo / Exclu, décimal ≥ 0 sur les colonnes numériques ; colonnes A–B grisées (ne pas modifier).
- [ ] **Step 6: `BrandDiscountsLegendSheet`** (titre `Légende`) — lignes fixes : cellule vide = inchangé ; 0 = mise à zéro ; coefficient 0 = pas de coefficient ; type `fix` = prix fixe, réduction/coefficient/ajout ignorés ; ne pas modifier ID client / ID marque ; nom et email ne sont pas réimportés.
- [ ] **Step 7: Action Filament** dans `ListUsers::getActions()` — `Action::make('exportBrandDiscounts')->label('Export remises marque')->form([Select::make('brand_id')->label('Marque')->options(Brand::orderBy('name')->pluck('name', 'id'))->searchable()->required()])->action(fn (array $data) => Excel::download(new BrandDiscountsExport(Brand::findOrFail($data['brand_id'])), …))`.
- [ ] **Step 8: Lancer** les tests → PASS. Vérification manuelle : ouvrir le `.xlsx` généré en local et contrôler listes déroulantes et volets figés.
- [ ] **Step 9: Commit** `feat(admin): export customers brand discounts per category to Excel`

---

### Task 3: Service d'import (analyse + application)

**Files:**
- Create: `app/Imports/BrandDiscounts/BrandDiscountImporter.php`
- Create: `app/DataObjects/BrandDiscounts/ImportReport.php`
- Test: `tests/Feature/BrandDiscounts/BrandDiscountImporterTest.php`

**Interfaces:**
- Consumes: `BrandDiscountColumns::{USER_FIELDS, FIELDS, parse(), categoriesFor()}` (Task 2).
- Produces:
  - `BrandDiscountImporter::analyse(Brand $brand, array $rows): ImportReport` — `$rows` = première feuille, ligne 0 = en-têtes. Aucune écriture.
  - `BrandDiscountImporter::apply(Brand $brand, array $rows): ImportReport` — relance `analyse()` ; si `hasErrors()` → retourne le rapport sans rien écrire ; sinon écrit en transaction.
  - `BrandDiscountImporter::readFile(string $path): array` — `Excel::toArray(…, $path)[0]`.
  - `ImportReport` (readonly) : `array $errors` (`['row' => int, 'column' => string, 'message' => string]`), `int $rulesCreated`, `int $rulesUpdated`, `int $usersUpdated`, `int $unchanged`, `hasErrors(): bool`.

- [ ] **Step 1: Écrire les tests qui échouent** (fichier de test construit avec les helpers de Task 2 : `$rows = [$sheet->headings(), ...$sheet->array()]`, puis modification de cellules)

```php
it('writes nothing on an unchanged round trip', ...);                 // report: 0 créées / 0 modifiées ; updated_at inchangés
it('updates an existing rule', ...);                                   // Rings réduction 10 → 15 ; rulesUpdated = 1
it('creates a rule with defaults when a cell is filled for a category without rule', ...);
// Watches Réduction 5, autres cellules vides → reduction 5, coefficient 0, addition 0, default, false, false
it('treats empty cells as unchanged and zero as a value', ...);        // vide → 12.5 conservé ; 0 → 0
it('updates external reference, franco and shipping price only', ...); // Nom/Email modifiés dans le fichier → ignorés
it('never touches meta rules', ...);                                   // règle meta sur Rings intacte
it('reports duplicate rules instead of guessing', ...);                // 2 lignes catégorie pour le trio → erreur, rien écrit
it('validates values', ...);
// client inconnu ; réduction 150 (> 100) ; coefficient -1 ; ajout 'abc' ; type 'promo' ; booléen 'peut-être'
// → une erreur par cas avec numéro de ligne Excel (index + 1) et en-tête de colonne ; apply() n'écrit rien
it('syncs disabled items when exclusion changes without firing the observer', function (): void {
    Queue::fake();
    // Exclu non → oui sur Rings
    // Alpha a 2 items dans Rings ; le client n'a aucun panier ouvert
    expect(UserDisable::where('user_id', $customer->id)->count())->toBe(2); // UpdateListDisabled exécuté via dispatchSync
    Queue::assertNotPushed(ProcessUpdatePriceCart::class);                  // l'observer n'a pas été déclenché
    // Exclu oui → non au second import → 0 UserDisable
});
it('refreshes only items of the brand in the customer open carts', function (): void {
    Queue::fake();
    // panier ouvert avec 1 item Alpha + 1 item Beta ; réduction Alpha modifiée
    Queue::assertPushed(ProcessUpdatePriceCart::class, 1);
});
// + les 5 tests de la section Review Focus
```

- [ ] **Step 2: Lancer** `php artisan test --filter=BrandDiscountImporterTest` → FAIL.
- [ ] **Step 3: Implémenter `analyse()`**
  - En-têtes : `ID client` et `ID marque` obligatoires, sinon erreur globale (ligne 1). Colonnes de catégorie via `parse()` ; `category_id` absent de `categoriesFor($brand)` → erreur globale.
  - Ligne entièrement vide → ignorée. `ID marque` ≠ `$brand->id` → erreur.
  - Normalisation : `trim`, virgule → point, `round(…, 2)` ; type de prix `strtolower(trim())` puis `PriceTypeEnum::tryFrom` ; booléens : `oui|1|x|true` / `non|0|false` (insensible à la casse).
  - Bornes : réduction 0–100, coefficient 0–100, ajout ≥ 0, franco ≥ 0, frais de port ≥ 0.
  - Diff : comparer aux valeurs actuelles **lues via les accesseurs** et arrondies à 2 décimales ; ne compter que les vrais changements.
  - Règles existantes chargées en une requête (même critère strict que Task 2) ; plus d'une ligne pour un trio → erreur « doublon, lancer user-brands:dedupe ».
- [ ] **Step 4: Implémenter `apply()`** — `DB::transaction` ; `UserBrand` : `fill()` des seuls champs modifiés + `saveQuietly()` ; si `is_excluded` a changé → `UpdateListDisabled::dispatchSync($rule)` ; `User` : `external_reference`, `franco`, `shipping_price` + `save()`. Après commit, pour chaque client dont une règle a changé : items distincts de la marque présents dans ses paniers `CartStatusEnum::OPEN` → `ProcessUpdatePriceCart::dispatch(item: $item, user: $user)`.
- [ ] **Step 5: Lancer** les tests → PASS.
- [ ] **Step 6: Commit** `feat(admin): import brand discounts from Excel with dry-run analysis`

---

### Task 4: Page Filament d'import

**Files:**
- Create: `app/Filament/Pages/ImportBrandDiscounts.php`
- Create: `resources/views/filament/pages/import-brand-discounts.blade.php`
- Test: `tests/Feature/BrandDiscounts/ImportBrandDiscountsPageTest.php`

**Interfaces:**
- Consumes: `BrandDiscountImporter::{readFile, analyse, apply}`, `ImportReport` (Task 3).
- Produces: page `/admin/import-brand-discounts`, groupe de navigation `Users`, libellé « Import remises marque ».

- [ ] **Step 1: Écrire les tests qui échouent**

```php
it('is reserved to admins', ...)->with([admin 200, agent 403, customer 403]); // même dataset que SalesStatsPageTest
it('shows the analysis without writing', ...);     // upload + call('analyse') → assertSee('1 règle modifiée') ; base inchangée
it('applies after a clean analysis', ...);          // call('analyse') puis call('apply') → base modifiée, notification succès
it('cannot apply when the analysis has errors', ...); // bouton Appliquer absent / call('apply') ne modifie rien, erreurs listées
```

- [ ] **Step 2: Lancer** → FAIL.
- [ ] **Step 3: Implémenter la page** sur le modèle de `ImportCSV` : formulaire `Select brand_id` (requis) + `FileUpload file` (`.xlsx` uniquement, un seul fichier) ; propriété `?array $report` (tableau sérialisable pour Livewire) ; `analyse()` remplit `$report` ; `apply()` relit le fichier, appelle `apply()`, notifie (`Filament\Notifications\Notification`) puis remet le formulaire à zéro. `canAccess` / `shouldRegisterNavigation` = admin. Vue : résumé chiffré, tableau des erreurs (ligne, colonne, message), bouton « Appliquer » visible seulement si `report` sans erreurs ; changer de fichier ou de marque efface `$report`.
- [ ] **Step 4: Lancer** les tests → PASS.
- [ ] **Step 5: Vérification complète** — `php artisan test` (tout vert), `vendor/bin/phpstan analyse` (0 erreur), `vendor/bin/pint --test` puis contrôle qu'aucun `mb_trim` n'a été introduit (`rg mb_trim app`).
- [ ] **Step 6: Commit** `feat(admin): add brand discounts import page`
