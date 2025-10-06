# Optimisation de la génération des catalogues PDF

## Problèmes résolus

### Problèmes initiaux
1. **Mémoire** : Les commandes chargeaient tous les produits en mémoire simultanément (300+ pages)
2. **Timeout** : Un seul catalogue de 300+ pages prenait trop de temps (timeout serveur code 143)
3. **Code dupliqué** : Chaque commande (Homme, Femme, Enfant) avait le même code répété

### Solution implémentée

**1. Séparation par genre**
- **4 catalogues distincts** au lieu d'un seul énorme :
  - `app:create-catalogue-homme` (Gents)
  - `app:create-catalogue-femme` (Ladies)
  - `app:create-catalogue-enfant` (Kids)
  - `app:create-catalogue-unisex` (produits sans genre)

**2. Génération par groupe de catalogue**
- Chaque catalogue est généré **par groupe** (`catalog_group`)
- Traitement séquentiel avec libération mémoire entre chaque groupe
- Utilisation de `select()` optimisé pour charger uniquement les colonnes nécessaires

**3. Classe abstraite réutilisable**
- `AbstractCatalogueCommand` contient toute la logique commune
- Chaque commande spécifique n'a besoin que de définir :
  - Le nom du fichier final (ex: "Hommes")
  - Le filtre de genre (ex: "Gents", "Ladies", "Kids", null)

**4. Gestion de la mémoire**
- `unset()` des variables après chaque groupe
- `gc_collect_cycles()` pour forcer le garbage collector PHP
- Affichage de l'utilisation mémoire en temps réel

**5. Fusion des PDFs**
- Les PDFs individuels sont fusionnés avec `pdftk` ou `ghostscript`
- Ces outils travaillent en streaming (pas de chargement complet en mémoire)

## Installation des dépendances

### pdftk (recommandé - plus léger)
```bash
# Ubuntu/Debian
sudo apt-get install pdftk

# Laravel Sail
./vendor/bin/sail root-shell
apt-get update && apt-get install -y pdftk
```

### Ghostscript (fallback automatique)
```bash
# Ubuntu/Debian
sudo apt-get install ghostscript

# Laravel Sail
./vendor/bin/sail root-shell
apt-get update && apt-get install -y ghostscript
```

## Utilisation

### Génération complète par genre

```bash
# Hommes (Gents)
php artisan app:create-catalogue-homme

# Femmes (Ladies)
php artisan app:create-catalogue-femme

# Enfants (Kids)
php artisan app:create-catalogue-enfant

# Sans genre (Unisex)
php artisan app:create-catalogue-unisex

# Avec Sail
./vendor/bin/sail artisan app:create-catalogue-homme
```

### Mode sans fusion (RECOMMANDÉ pour éviter timeouts)

```bash
# Générer sans fusionner (garde les PDFs par groupe)
php artisan app:create-catalogue-homme --skip-merge

# Fusionner plus tard quand tous les groupes sont générés
php artisan app:create-catalogue-homme --merge-only
```

### En cas de timeout (code 143)

Si la commande est tuée avant la fin, utilisez `--resume` :

```bash
# 1. Première exécution (s'arrête au groupe 358)
php artisan app:create-catalogue-homme --skip-merge

# 2. Reprendre là où ça s'est arrêté
php artisan app:create-catalogue-homme --skip-merge --resume=358

# 3. Une fois TOUS les groupes générés, fusionner
php artisan app:create-catalogue-homme --merge-only
```

### Générer uniquement certains groupes

```bash
# Générer seulement les groupes 010, 020 et 030
php artisan app:create-catalogue-femme --only=010,020,030
```

### Générer tous les catalogues en parallèle

```bash
# Lancer les 4 catalogues en même temps (terminaux séparés ou background)
php artisan app:create-catalogue-homme --skip-merge &
php artisan app:create-catalogue-femme --skip-merge &
php artisan app:create-catalogue-enfant --skip-merge &
php artisan app:create-catalogue-unisex --skip-merge &

# Attendre que tous soient finis, puis fusionner chacun
php artisan app:create-catalogue-homme --merge-only
php artisan app:create-catalogue-femme --merge-only
php artisan app:create-catalogue-enfant --merge-only
php artisan app:create-catalogue-unisex --merge-only
```

## Avantages

- ✅ **Utilisation mémoire réduite** de 90%+ par catalogue
- ✅ **Pas de limite mémoire PHP nécessaire** (peut revenir à 512M ou 1G)
- ✅ **4 catalogues ciblés** au lieu d'un énorme (plus rapide, plus utile)
- ✅ **Progression visible** pendant la génération avec temps et mémoire
- ✅ **Résilience aux timeouts** : reprise possible avec `--resume`
- ✅ **Résilience** : en cas d'échec sur un groupe, les autres continuent
- ✅ **PDFs partiels disponibles** pour debug si besoin
- ✅ **Mode skip-merge** : évite les timeouts en séparant génération et fusion
- ✅ **Code maintenable** : classe abstraite réutilisable, DRY (Don't Repeat Yourself)
- ✅ **Filtrage flexible** : `--only` pour générer des groupes spécifiques

## Performance estimée

- **Avant** :
  - 1 catalogue de 300+ pages
  - 300+ pages × eager loading = 2-4GB RAM
  - Timeout fréquent (tué au groupe 31/69)

- **Après** :
  - 4 catalogues séparés (Hommes, Femmes, Enfants, Unisex)
  - Chaque catalogue : N groupes × 10-50MB par groupe = ~100-500MB RAM max
  - Pas de timeout (génération par petits morceaux avec --skip-merge)

## Architecture du code

```
AbstractCatalogueCommand.php      ← Classe de base avec toute la logique
├── CreateCatalogueHommeCommand.php    (Gents)
├── CreateCatalogueFemmeCommand.php    (Ladies)
├── CreateCatalogueEnfantCommand.php   (Kids)
└── CreateCatalogueUnisexCommand.php   (sans genre)
```

Chaque commande spécifique :
- Hérite de `AbstractCatalogueCommand`
- Définit uniquement `getFileName()` et `getGenderFilter()`
- Bénéficie automatiquement de toutes les optimisations

## Fichiers générés

```
storage/app/public/pdf/
├── catalog_Hommes_group_010.pdf    ← Fichiers temporaires par groupe
├── catalog_Hommes_group_015.pdf
├── ...
├── catalog_Hommes.pdf              ← Fichier final fusionné
├── catalog_Femmes_group_010.pdf
├── ...
├── catalog_Femmes.pdf
├── catalog_Enfants.pdf
└── catalog_Unisex.pdf
```

## Notes techniques

- Le template Blade `pdf.catalog` a été optimisé avec des vérifications `@if`
- Les requêtes utilisent `select()` pour limiter les colonnes chargées
- Les relations sont eager-loadées avec des `select()` spécifiques
- Le filtre de genre utilise `whereHas()` sur `meta_id = 1`
- Les produits sans genre utilisent `whereDoesntHave()` pour exclure Gents/Ladies/Kids
- Les fichiers temporaires sont automatiquement nettoyés après fusion
- La commande détecte automatiquement pdftk ou ghostscript
