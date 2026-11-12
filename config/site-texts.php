<?php

declare(strict_types=1);

/*
 * "Textes du site" admin page: how the translation keys are grouped and named.
 *
 * zones: id => label, display order, and match rules [file => key prefix].
 *        A key goes to the zone with the longest matching prefix; '' matches
 *        the whole file. Keys of files without rule go to "other".
 * labels: readable names, 'file.key' => name. Without one, the name is
 *         derived from the key ("location.invoice" → "Location › Invoice").
 * long: 'file.key' of long texts without html tags (rich editor anyway).
 * untranslated_exceptions: values allowed to be identical to French in
 *                          NL/EN (brands, product names…).
 */
return [
    'zones' => [
        'home' => ['label' => "Page d'accueil", 'order' => 10, 'match' => ['home' => '']],
        'footer' => ['label' => 'Pied de page', 'order' => 20, 'match' => ['home' => 'footer.']],
        'nav' => ['label' => 'Menu et en-tête', 'order' => 30, 'match' => ['nav' => '']],
        'about' => ['label' => 'Page « Nous connaître »', 'order' => 40, 'match' => ['about' => '']],
        'legal' => ['label' => 'Pages légales et CGV', 'order' => 50, 'match' => ['legal' => '']],
        'listing' => ['label' => 'Listes de produits, filtres et promotions', 'order' => 60, 'match' => ['listing' => '']],
        'item' => ['label' => 'Fiche produit et bandeaux', 'order' => 70, 'match' => ['item' => '']],
        'cart' => ['label' => 'Panier et commande', 'order' => 80, 'match' => ['cart' => '']],
        'order' => ['label' => 'PDF bon de commande', 'order' => 90, 'match' => ['order' => '']],
        'emails' => ['label' => 'E-mails envoyés aux clients', 'order' => 100, 'match' => ['emails' => '']],
        'user' => ['label' => 'Compte client et inscription', 'order' => 110, 'match' => ['user' => '']],
        'contact' => ['label' => 'Page contact', 'order' => 120, 'match' => ['contact' => '']],
        'auth' => ['label' => 'Connexion et mot de passe', 'order' => 130, 'match' => ['auth' => '', 'passwords' => '', 'pagination' => '']],
        'other' => ['label' => 'Autres textes', 'order' => 800, 'match' => []],
        'validation' => ['label' => 'Messages d’erreur des formulaires', 'order' => 900, 'match' => ['validation' => ''], 'technical' => true],
    ],

    'labels' => [],

    'long' => [],

    'untranslated_exceptions' => ['Cetronic', 'Best Seller'],
];
