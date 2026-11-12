# Plan de travail — Modernisation Cetronic

> Dernière mise à jour : 2026-06-13  
> Objectifs : stabilité, responsive design, mise à jour des composants (PHP 8.4, Laravel 11, Filament v3)

---

## Phase 1 — Stabilisation immédiate
*Durée estimée : 1-2 semaines — sans risque, sans breaking changes*

### 1.1 PHP 8.3 → 8.4
- [ ] Vérifier compatibilité de tous les packages avec PHP 8.4
- [ ] Mettre à jour PHP sur le serveur Forge
- [ ] Mettre à jour l'image Docker dans `docker-compose.yml` (Sail)
- [ ] Lancer les tests après upgrade : `./vendor/bin/sail pest`
- [ ] Vérifier PHPStan niveau 5 : `./vendor/bin/sail bin duster lint`

### 1.2 Dépendances Composer
- [ ] Résoudre le `block-insecure` temporaire (fait, mais à reconsidérer après migration Laravel 11)
- [ ] Mettre à jour `intervention/image` ^2.7 → ^3.0 (v2 non maintenu)
- [ ] Vérifier compatibilité `moneyphp/money` ^4.0 (non maintenu activement)
- [ ] Supprimer `laravel/breeze` des dépendances dev si inutile en prod
- [ ] Exécuter `composer update` propre après PHP 8.4

### 1.3 Configuration Queue
- [ ] Changer `QUEUE_CONNECTION=sync` → `redis` dans `.env` de prod (Forge)
- [ ] Vérifier que Horizon est bien actif sur le serveur (`php artisan horizon:status`)
- [ ] Ajouter `php artisan horizon:terminate` dans le script de déploiement Forge
  → **Évite le bug SMTP Horizon (voir incident juin 2026)**
- [ ] Documenter la procédure de restart Horizon dans ce fichier

### 1.4 Refactoring code — Quick wins
- [ ] Créer un trait `HasMoneyAttributes` dans `app/Concerns/`
  → Déduplique les 10 attributs prix/100 identiques dans `Order` et autres modèles
- [ ] Fusionner la logique commune entre `BrandService` et `CategoryService` (~40% de duplication)
- [ ] Supprimer les `ini_set('max_execution_time', '300')` dans les Jobs → utiliser `timeout` sur le Job

### 1.5 Sécurité
- [ ] Auditer les 19 security advisories ignorées et documenter pourquoi chacune est acceptable
- [ ] Vérifier que `APP_DEBUG=false` en production
- [ ] Vérifier les headers HTTP sécurité (CSP, X-Frame-Options, etc.)

---

## Phase 2 — Migration Filament v2 → v3 + Laravel 11
*Durée estimée : 6-8 semaines — breaking changes importants*

> **Prérequis :** Filament v3 requiert Laravel 10+ et Livewire v3. Faire Livewire v2→v3 en même temps.

### 2.1 Préparation
- [ ] Créer une branche dédiée : `feature/laravel-11-filament-v3`
- [ ] Lire le guide officiel de migration Filament v2 → v3 : https://filamentphp.com/docs/3.x/upgrade-guide
- [ ] Lire le guide migration Livewire v2 → v3
- [ ] Augmenter la couverture de tests **avant** migration (filet de sécurité)

### 2.2 Migration Livewire v2 → v3 (11 composants)
- [ ] `FormItem` — revoir lifecycle hooks
- [ ] `SearchItem` — revoir lifecycle hooks
- [ ] `ListItems` — le plus complexe, lazy loading changé
- [ ] `CartCounter` — simple
- [ ] `PriceCart` / `PriceTotalCart` — simple
- [ ] `DeleteCart` — simple
- [ ] `FormCart` — revoir validation
- [ ] `LocationCart` — revoir validation
- [ ] `SearchMetas` — simple
- [ ] `ImportPictures` (Filament Page) — très complexe, upload + progress

### 2.3 Migration Filament Resources (ordre par complexité croissante)
- [ ] `MessageResource` (97 lignes) — simple
- [ ] `PubResource` (113 lignes) — simple
- [ ] `CustomerTypeResource` — simple
- [ ] `CategoryResource` (127 lignes) — moyen
- [ ] `BrandResource` (130 lignes) — moyen
- [ ] `CartResource` (137 lignes) — moyen
- [ ] `ItemPublishLogResource` (136 lignes) — moyen
- [ ] `PdfCatalogResource` — moyen
- [ ] `OrderResource` (162 lignes) — moyen
- [ ] `BrandsRelationManager` (121 lignes) — moyen
- [ ] `ItemResource` (320 lignes) — complexe, SpatieMediaLibrary upload
- [ ] `UserResource` (371 lignes) — le plus complexe, tabs, relations, rôles

### 2.4 Migration Filament Pages custom
- [ ] `EditOrder.php` / `CreateOrder.php` — moyen
- [ ] `ListItems.php` / `EditItem.php` / `CreateItem.php` — moyen
- [ ] `ImportPictures.php` (168 lignes) — très complexe, logique métier custom

### 2.5 Migration Laravel 10 → 11
- [ ] Lire le guide officiel : https://laravel.com/docs/11.x/upgrade
- [ ] Fusionner `app/Http/Kernel.php` → nouveau format middleware Laravel 11
- [ ] Migrer `config/` — Laravel 11 supprime certains fichiers de config par défaut
- [ ] Vérifier `app/Providers/` — AppServiceProvider, EventServiceProvider simplifiés
- [ ] Mettre à jour `bootstrap/app.php` (nouveau format Laravel 11)
- [ ] Lancer les tests, corriger les régressions
- [ ] Mettre à jour Forge pour utiliser PHP 8.4 + Laravel 11

### 2.6 Packages à mettre à jour en même temps
- [ ] `laravel/sanctum` → version compatible Laravel 11
- [ ] `laravel/scout` → version compatible Laravel 11
- [ ] `laravel/horizon` → version compatible Laravel 11
- [ ] `barryvdh/laravel-dompdf` → dernière version
- [ ] `maatwebsite/excel` → version compatible Laravel 11
- [ ] `mcamara/laravel-localization` → vérifier support Laravel 11
- [ ] `spatie/laravel-media-library` → version compatible Filament v3

### 2.7 Décision du 12/11/2026 — migration reportée, inventaire à jour
**Décidé :** on fait la migration complète vers Filament v3 + Livewire 3, mais **plus tard**, dès qu'il y a du temps.
La page « Textes du site » (`docs/specs/2026-11-12-textes-du-site.md`) sera construite **après** cette migration, directement en v3.

**Inventaire (12/11/2026) :**
- Admin : 12 resources, 12 relation managers, 35 pages de resource, 4 pages custom, 3 widgets (~4 300 lignes), 13 vues Blade custom.
- Front : 13 composants Livewire, 22 inclusions dans les vues, 21 appels `emit` / `dispatchBrowserEvent` à passer en `dispatch`.
- Estimation de la migration Filament + Livewire seule : **3 à 6 jours**.

**Pièges identifiés :**
- `wire:model` devient différé en Livewire 3 : `SearchItem` (et tout champ qui doit réagir à la frappe) doit passer en `wire:model.live`.
- Livewire 3 embarque Alpine : retirer l'import `alpinejs` du site (`resources/js`) pour éviter un double chargement.
- `outhebox/laravel-translations` 0.0.6 dépend de Livewire 2 (wireui, wire-elements/modal) : **incompatible**. À remplacer par la page « Textes du site » (même tables `ltu_*` ou migration des données).
- Tests Filament : les helpers de test changent (`callPageAction`, `assertTableActionExists`…).
- Plugin Spatie Media Library pour Filament : passer à la version v3.
- Accès admin : implémenter `FilamentUser::canAccessPanel()` (remplace `canAccessFilament()`), sinon 403 en prod.
- Prod en PHP 8.3 : vérifier les contraintes de version des paquets avant de déployer.

**Ordre retenu :** 1) migration Filament v3 + Livewire 3 (sans Laravel 11), 2) page « Textes du site » en v3, 3) Laravel 11.

---

## Phase 3 — Frontend responsive
*Durée estimée : 4-6 semaines*

> **Prérequis :** stabiliser le backend (Phase 1 + 2) avant de toucher le frontend.

### 3.1 Choix du framework CSS
- [ ] **Décision à prendre :** abandonner Bootstrap 5 et migrer vers TailwindCSS
  → TailwindCSS est déjà dans `package.json`, Alpine.js est déjà utilisé
  → Bootstrap et Tailwind en parallèle = conflits, poids inutile
- [ ] Supprimer Bootstrap CDN du layout `default.blade.php`
- [ ] Supprimer OwlCarousel CDN (remplacer par solution Alpine.js)
- [ ] Configurer TailwindCSS correctement avec purge des classes inutilisées
- [ ] Migrer les CSS custom (`nav.css`, `menu.css`, `style.css`, `footer.css`, `cart.css`) vers Tailwind

### 3.2 Layout master — refonte responsive
- [ ] `resources/views/front/layout/default.blade.php` — refonte complète
  - Navigation mobile (hamburger menu Alpine.js)
  - Header responsive
  - Footer responsive
- [ ] Tester sur : mobile 375px, tablette 768px, desktop 1280px+

### 3.3 Pages publiques — responsive
- [ ] `front/pages/home` — carousel, banners, featured products
- [ ] `front/pages/listing` — grille produits (1/2/3/4 colonnes selon écran)
- [ ] `front/pages/item` — page détail produit
- [ ] `front/pages/cart` — panier, étapes commande
- [ ] `front/pages/contact` / `help`

### 3.4 Espace utilisateur — responsive
- [ ] `front/users/dashboard`
- [ ] `front/users/orders` — liste et détail commandes
- [ ] `front/users/profile`
- [ ] `front/users/locations`

### 3.5 Composants Blade — responsive
- [ ] `x-nav-component` — navigation principale
- [ ] `x-menu-component` — menu catégories
- [ ] Composants Livewire : vérifier rendu mobile

### 3.6 Performance frontend
- [ ] Configurer Vite correctement (asset versioning, lazy loading)
- [ ] Optimiser les images (WebP, lazy loading natif)
- [ ] Vérifier score Lighthouse mobile (objectif > 80)

---

## Phase 4 — Tests & qualité
*Peut être fait en parallèle des autres phases*

### 4.1 Couverture tests — passer de 15% à ~60%
- [ ] Tester `CartService` (le plus complexe, 246 lignes)
- [ ] Tester `OrderService` (142 lignes, PDF + shipping)
- [ ] Tester `ItemService` (206 lignes, search + export)
- [ ] Tester les Form Requests (validation rules)
- [ ] Tester les Observers (comportements sur create/update)
- [ ] Tester les Jobs (emails, PDF generation)
- [ ] Tester les Resources Filament (après migration v3)

### 4.2 PHPStan
- [ ] Vérifier niveau actuel : `./vendor/bin/sail bin duster lint`
- [ ] Objectif : monter de niveau 5 à niveau 6-7 progressivement

### 4.3 CI/CD
- [ ] Vérifier que les tests tournent en CI avant deploy
- [ ] Ajouter `php artisan horizon:terminate` dans le script de déploiement Forge

---

## Incidents à ne pas répéter

| Date | Incident | Cause | Fix |
|------|----------|-------|-----|
| 2026-06-13 | Emails non envoyés en prod (2 jours) | Horizon non redémarré après problème Brevo | Ajouter `horizon:terminate` dans deploy script |
| 2026-06-13 | `composer update` bloqué | Laravel 10 fin de vie, security advisories bloquants | `block-insecure: false` temporaire, migration L11 à planifier |

---

## Estimation globale

| Phase | Durée | Risque |
|-------|-------|--------|
| Phase 1 — Stabilisation | 1-2 semaines | Faible |
| Phase 2 — Filament v3 + Laravel 11 | 6-8 semaines | Élevé |
| Phase 3 — Frontend responsive | 4-6 semaines | Moyen |
| Phase 4 — Tests | En parallèle | Faible |
| **Total** | **~4 mois** | |
