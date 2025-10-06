# Optimisation de la génération du catalogue PDF

## Problèmes résolus

### Problème initial
La commande `CreateCatalogueHommeCommand` chargeait **tous les produits en mémoire** simultanément (300+ pages), causant des dépassements mémoire critiques même avec `memory_limit=-1`.

### Solution implémentée

**1. Génération par groupe de catalogue**
- Au lieu de charger tous les produits, la commande génère un PDF **par groupe** (`catalog_group`)
- Chaque groupe est traité séquentiellement avec libération mémoire entre chaque génération

**2. Fusion des PDFs**
- Les PDFs individuels sont fusionnés en un seul fichier final avec `pdftk` ou `ghostscript`
- Ces outils travaillent en streaming et n'ont pas besoin de charger tout le PDF en mémoire

**3. Gestion de la mémoire**
- `unset()` des variables après chaque groupe
- `gc_collect_cycles()` pour forcer le garbage collector PHP
- Nettoyage des fichiers temporaires après fusion

## Installation des dépendances

### Option 1: pdftk (recommandé - plus léger)
```bash
# Ubuntu/Debian
sudo apt-get install pdftk

# Docker (ajoutez au Dockerfile)
RUN apt-get update && apt-get install -y pdftk
```

### Option 2: Ghostscript (fallback automatique)
```bash
# Ubuntu/Debian
sudo apt-get install ghostscript

# Docker (ajoutez au Dockerfile)
RUN apt-get update && apt-get install -y ghostscript
```

### Laravel Sail
Ajoutez au `docker-compose.yml` ou créez un `Dockerfile` personnalisé :

```yaml
services:
    laravel.test:
        build:
            context: ./vendor/laravel/sail/runtimes/8.3
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        # ... reste de la config
```

Créez `vendor/laravel/sail/runtimes/8.3/Dockerfile` (si personnalisation nécessaire) :
```dockerfile
FROM sail-8.3/app

RUN apt-get update && apt-get install -y \
    pdftk \
    ghostscript \
    && apt-get clean
```

## Utilisation

```bash
# Exécution normale
php artisan app:create-catalogue-homme

# Avec Sail
./vendor/bin/sail artisan app:create-catalogue-homme
```

## Avantages

- ✅ **Utilisation mémoire réduite** de 90%+
- ✅ **Pas de limite mémoire PHP nécessaire** (peut revenir à 512M ou 1G)
- ✅ **Progression visible** pendant la génération
- ✅ **Résilience** : en cas d'échec sur un groupe, les autres continuent
- ✅ **PDFs partiels disponibles** pour debug si besoin

## Performance estimée

- **Avant** : 300+ pages × eager loading = 2-4GB RAM
- **Après** : N groupes × 10-50MB par groupe = ~100-500MB RAM max

## Notes techniques

- Le template Blade a été optimisé avec des vérifications `@if` pour éviter les erreurs
- Attribut `loading="lazy"` ajouté (bien que peu utile pour PDF statique)
- Les fichiers temporaires sont automatiquement nettoyés
- La commande détecte automatiquement pdftk ou ghostscript
