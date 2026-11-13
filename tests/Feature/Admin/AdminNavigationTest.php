<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

/**
 * @return array<string, list<string>> group label => entry labels, as built for the current user
 */
function adminNavigation(): array
{
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::getCurrentPanel()->navigation(true);

    return collect(Filament::getNavigation())
        ->mapWithKeys(fn(NavigationGroup $group): array => [
            (string) $group->getLabel() => collect($group->getItems())
                ->map(fn(NavigationItem $item): string => (string) $item->getLabel())
                ->values()
                ->all(),
        ])
        ->all();
}

it('shows the French menu in order to an admin', function (): void {
    loginAsAdmin();
    app()->setLocale('fr');

    expect(adminNavigation())->toBe([
        '' => ['Tableau de bord'],
        'Ventes' => ['Commandes', 'Paniers', 'Statistiques ventes'],
        'Catalogue' => ['Produits', 'Marques', 'Catégories', 'Pubs', 'Traductions des métas', 'Journal de publication'],
        'Clients' => ['Clients', 'Types de client', 'Import remises marque'],
        'Contenu' => ['Textes du site', 'Messages', 'Catalogues PDF'],
        'Imports' => ['Import CSV', 'Import photos'],
    ]);
});

it('gives every menu entry its own icon', function (): void {
    loginAsAdmin();
    adminNavigation();

    $icons = collect(Filament::getNavigation())
        ->flatMap(fn(NavigationGroup $group): iterable => $group->getItems())
        ->map(fn(NavigationItem $item): ?string => $item->getIcon());

    expect($icons->unique()->count())->toBe($icons->count());
});

it('shows an agent no more entries than before', function (): void {
    loginAsAgent();
    app()->setLocale('fr');

    // Before the redesign an agent saw: Tableau de bord, Customer Types, Messages, Pdf Catalogs.
    expect(adminNavigation())->toBe([
        '' => ['Tableau de bord'],
        'Clients' => ['Types de client'],
        'Contenu' => ['Messages', 'Catalogues PDF'],
    ]);
});
