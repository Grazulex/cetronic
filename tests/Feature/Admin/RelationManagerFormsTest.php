<?php

declare(strict_types=1);

use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Filament\Resources\ItemResource\RelationManagers\MetasRelationManager;

use function Pest\Livewire\livewire;

/*
 * The render tests do not open the modals of the relation managers: their
 * form closures (Filament v3 injects Get/Set objects) are checked here.
 */

it('opens the create meta modal of a product', function (): void {
    loginAsAdmin();
    $item = createItems(1)->first();

    livewire(MetasRelationManager::class, ['ownerRecord' => $item, 'pageClass' => EditItem::class])
        ->mountTableAction('create')
        ->assertHasNoTableActionErrors()
        ->assertOk();
});

it('opens the edit meta modal of a product', function (): void {
    loginAsAdmin();
    $item = createItems(1)->first();
    $meta = $item->metas()->first();

    livewire(MetasRelationManager::class, ['ownerRecord' => $item, 'pageClass' => EditItem::class])
        ->mountTableAction('edit', $meta)
        ->assertOk();
});
