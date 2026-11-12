# Cahier des charges — « Textes du site » (édition des traductions par le client)

> Date : 12 novembre 2026 · Statut : proposition, rien n'est développé
> Périmètre : points 1 et 2 de la réflexion du 12/11 + bouton « Traduire cette page » (remplace l'idée d'édition directe sur le site)

## 1. Contexte et problème

Les textes du site (≈ 490 clés, en français, néerlandais et anglais) sont gérés par le package `outhebox/laravel-translations` (v0.0.6), via l'interface `/translations`. La base de données (`ltu_phrases`) est la source de vérité ; les fichiers `lang/{fr,nl,en}/*.php` sont régénérés depuis la base (bouton « Publish » ou `translations:export` à chaque déploiement).

Le client et le développeur trouvent l'outil très difficile, pour six raisons :

1. Un texte est identifié par une clé technique (`order.location.invoice`), sans indication de la page où il apparaît.
2. Une langue par écran ; l'anglais (langue source) a un écran différent.
3. La recherche ne porte que sur une langue à la fois.
4. Il faut penser à cliquer sur « Publish », sinon rien ne change sur le site.
5. Les textes longs contiennent du HTML brut (`<p>`, `<br>`, `<ul>`) dans un champ texte.
6. Rien ne signale un texte manquant dans une langue (ex. : l'e-mail de bienvenue était vide en néerlandais).

## 2. Objectifs

- Le client trouve un texte **à partir de la page où il le voit**, sans connaître les clés.
- Il modifie les **trois langues en même temps**, au même endroit.
- Les modifications sont **en ligne dès l'enregistrement**.
- Les textes longs se modifient avec un **éditeur de texte riche**, sans HTML.
- Les **traductions manquantes** sont visibles.
- Le développeur n'intervient plus que pour **créer** de nouveaux textes (nouvelles fonctionnalités).

Hors périmètre : la traduction automatique, l'édition directe sur le site (cliquer sur un texte dans la page), la suppression de `/translations` (gardé pour le développeur).

## 3. Utilisateurs et accès

| Rôle | Accès |
|---|---|
| Admin (`role = admin`) | Page « Textes du site » complète + bouton « Traduire cette page » sur le site |
| Agent | Aucun accès (comme pour `/translations` aujourd'hui) |
| Client / invité | Aucun accès, le bouton n'est jamais rendu |

## 4. Point 1 — Page admin « Textes du site »

### 4.1 Emplacement

Nouvelle page Filament dans le groupe de navigation **« Contenu »** (à créer), libellée « Textes du site ». Elle lit et écrit les mêmes tables que le package (`ltu_phrases`, `ltu_translations`, `ltu_translation_files`) : aucune migration de données, les deux outils restent compatibles.

### 4.2 Écran principal

```
┌ Textes du site ─────────────────────────────────────────────────────────────────┐
│ 🔍 Rechercher dans tous les textes (FR, NL, EN) ou par nom…                       │
│ Zone : [Toutes ▾]   Afficher : (•) Tous ( ) À traduire ( ) Modifiés récemment     │
├──────────────────────────────────────────────────────────────────────────────────┤
│ ▸ Pied de page (14)                                                               │
│ ▾ PDF bon de commande (33)                                                        │
│   Adresse de facturation                                                          │
│   FR [Adresse de facturation   ]  NL [Factuuradres        ]  EN [Invoice address ] │
│   Total livraison                                                                 │
│   FR [Total livraison          ]  NL [Totaal verzending   ]  EN [Shipping total  ] │
│ ▸ E-mails envoyés aux clients (18)                                                │
│ ▸ Page « Nous connaître » (58)                                                    │
└──────────────────────────────────────────────────────────────────────────────────┘
```

- **Regroupement par zone**, dans des sections repliables. Le nombre de textes s'affiche à côté du nom de la zone.
- **Une ligne par texte** : un nom lisible + trois champs FR / NL / EN côte à côte. La clé technique n'apparaît qu'en petit, au survol (utile au développeur).
- **Recherche unique** : elle porte sur les trois langues et sur les noms lisibles. Les résultats sont surlignés et les sections concernées s'ouvrent.
- **Filtre « À traduire »** : textes vides dans une langue, ou identiques au français en NL/EN (hors noms propres et nombres, liste d'exceptions configurable).
- **Filtre « Modifiés récemment »** : textes modifiés depuis 30 jours (via `updated_at`), pour retrouver ce que l'on vient de changer.
- **Enregistrement par ligne** (bouton ou enregistrement à la sortie du champ). Un message confirme : « Texte enregistré et publié ».

### 4.3 Zones et noms lisibles

Fichier de configuration `config/site-texts.php` versionné, qui associe :

- chaque **groupe** de traduction à une **zone** lisible et à un ordre d'affichage ;
- les **clés importantes** à un **nom lisible** ; à défaut, le nom est déduit de la clé (`order.location.invoice` → « Location › Invoice »).

| Groupe | Zone proposée | Clés (EN) |
|---|---|---|
| `home` (sauf `footer.*`) | Page d'accueil | ~20 |
| `home.footer.*` | Pied de page | ~14 |
| `nav` | Menu et en-tête | 11 |
| `about` | Page « Nous connaître » | 58 |
| `legal` | Pages légales et CGV | 67 |
| `listing` | Listes de produits, filtres et promotions | 22 |
| `item` | Fiche produit et bandeaux | 28 |
| `cart` | Panier et commande | 32 |
| `order` | PDF bon de commande | 33 |
| `emails` | E-mails envoyés aux clients | 18 |
| `user` | Compte client et inscription | 54 |
| `contact` | Page contact | 18 |
| `auth`, `passwords`, `pagination` | Connexion et mot de passe | 10 |
| `validation` | Messages d'erreur des formulaires | 106 — **masqués par défaut** (case « Afficher les textes techniques ») |

Le fichier est pré-rempli par le développeur pour les zones à fort usage client (pied de page, PDF, e-mails, promotions, bandeaux). Le reste peut garder le nom déduit au départ, puis être complété au fil de l'eau.

### 4.4 Publication automatique

- Après chaque enregistrement : mise à jour de `ltu_phrases`, puis régénération des fichiers de langue (`translations:export`).
- En prod, OPcache garde ses réglages par défaut (revérification des fichiers toutes les 2 s) : le texte est visible sur le site **dans les 2 secondes**, sans redémarrage.
- Le bouton « Publish » de `/translations` n'est plus nécessaire pour le client.
- Si l'export échoue (droits disque, etc.), le message l'indique clairement et la valeur reste enregistrée en base. Elle sera publiée au prochain enregistrement ou au prochain déploiement.

### 4.5 Garde-fous

- **Variables** : un texte qui contient `:name`, `:count`, `:product`, `:reference`… doit garder les mêmes variables dans les trois langues. Sinon, avertissement bloquant : « Ce texte doit contenir :name ».
- **Pluriels** (`un produit|:count produits`) : un champ « singulier » et un champ « pluriel », plutôt que le `|` brut.
- **Texte vide** : autorisé, mais signalé en orange dans la ligne, et compté dans « À traduire ».
- **Historique** : la valeur précédente est conservée (table `site_text_revisions`) avec la date et l'admin qui a modifié. Un bouton « Annuler la dernière modification » est disponible par ligne.

## 5. Point 2 — Textes longs avec éditeur riche

### 5.1 Quels textes

Les textes qui contiennent du HTML (aujourd'hui 41 clés : pages légales, CGV, « Nous connaître », texte des promotions, corps des e-mails) sont marqués **« texte long »** dans `config/site-texts.php` ou détectés automatiquement (présence de balises).

### 5.2 Édition

```
┌ Pages légales et CGV › Conditions générales de vente › Réclamations ─────────┐
│ [ FR ] [ NL ] [ EN ]                                     (onglets de langue)  │
│ ┌─────────────────────────────────────────────────────────────────────────┐ │
│ │ B  I  🔗  • liste  1. liste                                              │ │
│ │ Toute réclamation doit nous parvenir, sous peine de déchéance, par      │ │
│ │ lettre recommandée dans les huit jours…                                 │ │
│ └─────────────────────────────────────────────────────────────────────────┘ │
│                                            [Annuler]  [Enregistrer et publier] │
└────────────────────────────────────────────────────────────────────────────┘
```

- Ouverture dans une fenêtre, depuis la ligne du texte (bouton « Modifier le texte »).
- **Éditeur riche Filament** (`RichEditor`) avec une barre réduite : gras, italique, lien, liste à puces, liste numérotée. Pas de titres, d'images ni de couleurs, pour garder la mise en page du site.
- Un onglet par langue dans la même fenêtre. Un seul enregistrement publie les trois.
- **Nettoyage du HTML** à l'enregistrement : seules les balises de la barre sont conservées (`p`, `br`, `strong`, `em`, `a[href]`, `ul`, `ol`, `li`). Les scripts et styles sont supprimés.
- Les **sauts de ligne des e-mails** (`<br><br>` aujourd'hui) sont convertis en paragraphes à l'ouverture, puis réécrits proprement à l'enregistrement.

## 6. Bouton « Traduire cette page »

### 6.1 Principe

Sur le site public, un admin connecté voit un petit bouton flottant **« Traduire cette page »**. Il ouvre la page « Textes du site » **filtrée sur les textes réellement utilisés par la page qu'il regardait**, dans la langue affichée.

### 6.2 Fonctionnement technique

1. **Collecte des clés** : le traducteur de Laravel est enveloppé (`$app->extend('translator', …)`) pour enregistrer chaque clé demandée (`__()`, `trans()`, `@lang`, `trans_choice`) pendant la requête. La collecte n'est active que si l'utilisateur est admin, sur les routes du site public : aucun coût pour les clients.
2. **Jeton de page** : un middleware génère un jeton au début de la requête et le partage avec la vue. À la fin de la requête, il stocke en cache (2 h) la liste des clés collectées, l'URL et la langue sous ce jeton.
3. **Bouton** : rendu dans le layout du site, il pointe vers `/admin/textes-du-site?page={jeton}`. La page admin lit le jeton et n'affiche que ces clés, regroupées par zone. Un bandeau rappelle : « Textes de la page /fr/watches/promo/nos-promos ».
4. **Index des usages** : à chaque collecte, la table `site_text_usages` (clé, URL, dernière vue) est mise à jour. La page admin peut alors afficher « Visible sur : /fr, /fr/about » sous chaque texte, même hors du bouton.

### 6.3 Limites connues (à accepter ou traiter)

- Les textes ajoutés après le chargement par Livewire (ex. : messages du panier après « Ajouter au panier ») ne sont pas captés par le premier affichage. Ils restent trouvables par la recherche.
- Les textes des **PDF** et des **e-mails** ne sont pas visibles sur une page du site. Ils sont accessibles par leur zone (« PDF bon de commande », « E-mails envoyés aux clients »). Option : un bouton « Textes de ce PDF » dans la fiche commande de l'admin.
- Les clés `validation.*` utilisées en coulisses par les formulaires sont exclues de la collecte.

## 7. Exigences non fonctionnelles

- **Compatibilité Filament** : l'admin est en Filament v2.17. `PLAN_TRAVAIL.md` prévoit Filament v3. Il faut décider avant le développement si la page est construite en v2 (et adaptée lors de la migration) ou après la migration.
- **Performance** : l'écran charge ≈ 1 500 valeurs (≈ 490 clés × 3 langues). Prévoir un chargement par zone (sections repliées) et une recherche côté serveur.
- **Accessibilité** : champs étiquetés par langue (« Factuuradres — néerlandais »), navigation au clavier, focus visible.
- **Sécurité** : page et bouton réservés au rôle admin ; HTML nettoyé ; jeton de page à usage limité dans le temps et lié à l'admin qui l'a généré.
- **Production** : PHP 8.3 (attention à Pint qui transforme `trim()` en `mb_trim()`). Sauvegarde automatique de `ltu_phrases` avant toute opération de masse.

## 8. Critères d'acceptation

1. Depuis la page des promotions, un admin clique sur « Traduire cette page ». Il arrive sur les seuls textes de cette page, modifie le titre en néerlandais, enregistre, recharge la page en néerlandais : le nouveau titre s'affiche (sans clic sur « Publish »).
2. Une recherche « Facturatie » trouve les trois textes concernés, chacun avec sa zone (« PDF bon de commande », « Panier et commande »).
3. Le filtre « À traduire » liste les textes vides en NL ou EN. Après traduction, ils disparaissent du filtre.
4. Modifier une section de CGV avec l'éditeur (gras + liste à puces) produit un HTML propre et un affichage correct sur la page CGV, dans les trois langues.
5. Supprimer `:name` du texte néerlandais de l'e-mail de bienvenue est refusé avec un message clair.
6. « Annuler la dernière modification » restaure la valeur précédente, et le site affiche à nouveau l'ancien texte.
7. Un agent ou un client ne voit ni le bouton ni la page (403).

## 9. Découpage proposé

| Étape | Contenu | Livrable |
|---|---|---|
| 1 | Page « Textes du site » : zones, 3 langues côte à côte, recherche, publication automatique | Utilisable par le client pour les textes courts |
| 2 | Config `site-texts.php` pré-remplie (pied de page, PDF, e-mails, promotions, bandeaux, menu) | Noms lisibles pour les textes les plus modifiés |
| 3 | Éditeur riche pour les textes longs + nettoyage HTML | CGV, pages légales, « Nous connaître », e-mails |
| 4 | Bouton « Traduire cette page » + index des usages | Accès direct depuis le site |
| 5 | Garde-fous (variables, pluriels) + historique et annulation | Sécurité d'usage |

Chaque étape est livrable seule. Les étapes 1 à 3 répondent à l'essentiel du besoin ; la 4 remplace l'édition directe sur le site.

## 10. Questions ouvertes

1. Construire en Filament v2 maintenant, ou attendre la migration v3 de `PLAN_TRAVAIL.md` ?
2. Garder `/translations` accessible au client, ou le réserver au développeur une fois la nouvelle page en place ?
3. Faut-il un bouton « Textes de ce PDF » dans la fiche commande de l'admin (cf. § 6.3) ?
4. Liste d'exceptions du filtre « À traduire » (marques, références, « Best Seller »…) : à valider avec le client.
