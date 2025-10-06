# Génération de Catalogue PDF avec Queues

## Problème résolu

**Forge limite les commandes à 5 minutes maximum** → Impossible de générer un catalogue de 300+ pages en une seule fois.

## Solution : Queues + Horizon + Jobs Laravel

Au lieu de générer le PDF d'un coup, on découpe le travail en **jobs indépendants** :

```
1 catalogue = 69 groupes = 69 jobs + 1 job de fusion
```

Chaque job génère **un groupe** (rapide, < 5min) puis un job final **fusionne tous les PDFs**.

## Architecture

```
Commande Artisan
    ↓
Crée un BATCH de 70 jobs
    ↓
┌─────────────────────────────────────┐
│ Job 1: Générer groupe 010           │ ← Exécuté par Horizon
│ Job 2: Générer groupe 015           │ ← En parallèle si workers++
│ ...                                 │
│ Job 69: Générer groupe 999          │
└─────────────────────────────────────┘
    ↓ (Quand TOUS sont terminés)
┌─────────────────────────────────────┐
│ Job 70: Fusionner tous les PDFs     │ ← Imagick ou pdftk/gs
│         avec Imagick                │
└─────────────────────────────────────┘
    ↓
📄 catalog_Full.pdf
```

## Utilisation

### Générer le catalogue complet

```bash
# Sur Forge (OBLIGATOIRE avec --queue)
php artisan app:create-catalogue --queue

# En local (Horizon doit tourner)
php artisan app:create-catalogue --queue
```

**Sortie :**
```
🚀 Génération du catalogue complet
📊 69 groupes trouvés
📤 Création des jobs...
✅ Batch créé avec ID: 9d6f8c37-...
📊 69 jobs de génération + 1 job de fusion

🔍 Suivre la progression dans Horizon:
   https://votre-app.com/horizon
   Batch ID: 9d6f8c37-...

📄 Le PDF final sera disponible à:
   storage/app/public/pdf/catalog_Full.pdf
```

### Suivre la progression

1. **Horizon Dashboard** : `https://votre-app.com/horizon`

   **Allez dans la queue `catalog`** pour voir uniquement les jobs de catalogue :
   - 🔍 Filtrer par queue : Sélectionnez `catalog` dans le dropdown
   - 📊 Voir les jobs en cours (Pending, Processing, Completed, Failed)
   - ⏱️ Temps d'exécution par job
   - 📈 Taux de réussite/échec
   - 🔄 Progression du batch en temps réel

2. **Logs Laravel** : `storage/logs/laravel.log`
   ```
   Génération du groupe 010 pour Full
   Groupe 010 généré : 45 produits en 12.3s | Mémoire: 78MB
   ...
   Début de la fusion des PDFs pour Full
   Fusion réussie avec Imagick
   Fusion terminée pour Full en 45.2s
   ```

## Configuration requise

### 1. Vérifier qu'Horizon tourne

```bash
# Sur Forge, ajouter un daemon dans l'interface
php artisan horizon

# En local
php artisan horizon
```

### 2. Vérifier Imagick (pour la fusion)

```bash
php -m | grep imagick
# Devrait afficher: imagick
```

Si Imagick n'est pas disponible, le système utilisera automatiquement `pdftk` ou `ghostscript` en fallback.

### 3. Queue dédiée `catalog`

Une queue **dédiée** a été créée pour isoler la génération de catalogue dans Horizon.

**Avantages :**
- 🔍 **Suivi facile** : Voir uniquement les jobs de catalogue dans Horizon
- 🎯 **Isolation** : Ne perturbe pas les autres jobs (emails, etc.)
- ⚙️ **Config dédiée** : Workers et timeout spécifiques

**Configuration dans `config/horizon.php` :**

```php
'supervisor-catalog' => [
    'connection' => 'redis',
    'queue' => ['catalog'],        // ← Queue dédiée
    'balance' => 'auto',
    'maxProcesses' => 10,          // ← 10 workers en production
    'memory' => 512,
    'tries' => 3,                  // ← Retry automatique 3 fois
    'timeout' => 600,
],
```

**Dans Horizon, vous verrez :**
- Queue `default` : Autres jobs (emails, etc.)
- Queue `catalog` : **Uniquement les jobs de catalogue** 🎯

## Jobs créés

### GenerateCatalogGroupJob

- **Responsabilité** : Générer le PDF d'un seul groupe
- **Timeout** : 5 minutes
- **Input** : `catalogGroup`, `genderFilter`, `catalogName`
- **Output** : `storage/app/public/pdf/catalog_Full_group_010.pdf`
- **Mémoire** : ~50-100MB par job

### MergeCatalogPdfsJob

- **Responsabilité** : Fusionner tous les PDFs de groupe en un seul
- **Timeout** : 10 minutes
- **Input** : `catalogName` (ex: "Full")
- **Output** : `storage/app/public/pdf/catalog_Full.pdf`
- **Méthode** : Imagick (priorité) puis pdftk/ghostscript

**Avantages d'Imagick :**
- Intégré PHP (pas de commande externe)
- Gestion mémoire optimisée
- Fonctionne bien sur Forge

## Avantages de cette approche

- ✅ **Pas de timeout Forge** (chaque job < 5min)
- ✅ **Parallélisation** : plusieurs groupes en même temps si workers multiples
- ✅ **Résilience** : retry automatique en cas d'échec
- ✅ **Progression visible** dans Horizon
- ✅ **Pas de limite mémoire PHP** (chaque job indépendant)
- ✅ **Logs détaillés** pour debug
- ✅ **Imagick natif** (pas besoin d'installer pdftk)

## Performance estimée

### Séquentiel (1 worker)
- 69 groupes × 15s/groupe = **~17 minutes**
- + Fusion : 1 minute
- **Total : ~18 minutes**

### Parallèle (10 workers)
- 69 groupes ÷ 10 workers = **~2 minutes**
- + Fusion : 1 minute
- **Total : ~3 minutes** 🚀

## Dépannage

### Les jobs ne démarrent pas
```bash
# Vérifier qu'Horizon tourne
php artisan horizon:status

# Si pas actif, démarrer
php artisan horizon
```

### Échec de fusion Imagick
Les logs montreront :
```
Échec fusion Imagick, tentative avec CLI
Fusion réussie avec CLI
```

C'est normal, le système fallback automatiquement sur pdftk/ghostscript.

### Job en échec
- Voir dans Horizon → Failed Jobs
- Les jobs réessaient automatiquement 3 fois
- Vérifier les logs dans `storage/logs/laravel.log`

## Commandes utiles

```bash
# Voir le statut d'Horizon
php artisan horizon:status

# Vider la queue (annuler tous les jobs en attente)
php artisan horizon:clear

# Relancer les jobs échoués
php artisan horizon:retry

# Voir les métriques
php artisan horizon:snapshot
```

## Catalogues par genre (optionnel)

Si besoin de catalogues séparés (Hommes, Femmes, etc.), les anciennes commandes sont toujours disponibles :

```bash
php artisan app:create-catalogue-homme --queue
php artisan app:create-catalogue-femme --queue
php artisan app:create-catalogue-enfant --queue
php artisan app:create-catalogue-unisex --queue
```

Chacune générera un PDF distinct filtré par genre.

## Fichiers générés

```
storage/app/public/pdf/
├── catalog_Full_group_010.pdf    ← Temporaires (supprimés après fusion)
├── catalog_Full_group_015.pdf
├── ...
├── catalog_Full_group_999.pdf
└── catalog_Full.pdf              ← 📄 FICHIER FINAL
```

Les fichiers `_group_*.pdf` sont automatiquement supprimés après la fusion réussie.
