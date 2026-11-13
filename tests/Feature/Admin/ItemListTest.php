<?php

declare(strict_types=1);

use App\Filament\Resources\ItemResource\Pages\ListItems;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Brand::factory()->create();
    Category::factory()->create();
    loginAsAdmin();

    $this->promo = Item::factory()->create(['is_published' => true, 'price_promo' => 9]);
    $this->online = Item::factory()->create(['is_published' => true, 'price_promo' => 0]);
    $this->offline = Item::factory()->create(['is_published' => false, 'price_promo' => 0]);
    $this->promo->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection();
});

it('filters products with the quick tabs', function (string $tab, array $visible, array $hidden): void {
    livewire(ListItems::class)
        ->set('activeTab', $tab)
        ->assertCanSeeTableRecords(array_map(fn(string $name) => $this->{$name}, $visible))
        ->assertCanNotSeeTableRecords(array_map(fn(string $name) => $this->{$name}, $hidden));
})->with([
    'tous' => ['tous', ['promo', 'online', 'offline'], []],
    'en ligne' => ['en-ligne', ['promo', 'online'], ['offline']],
    'hors ligne' => ['hors-ligne', ['offline'], ['promo', 'online']],
    'en promo' => ['promo', ['promo'], ['online', 'offline']],
    'sans photo' => ['sans-photo', ['online', 'offline'], ['promo']],
]);

it('shows only the useful columns by default', function (): void {
    livewire(ListItems::class)
        ->assertCanRenderTableColumn('reference')
        ->assertCanRenderTableColumn('brand.name')
        ->assertCanRenderTableColumn('price_promo')
        ->assertCanRenderTableColumn('orders_count')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at')
        ->assertCanNotRenderTableColumn('master.reference')
        ->assertCanNotRenderTableColumn('media_count');
});

it('shows French column headers', function (): void {
    livewire(ListItems::class)->assertSee(['Référence', 'Marque', 'Catégorie', 'Prix B2B', 'Promo', 'Statut', 'Vendus']);
});

it('keeps every filter, action and bulk action', function (): void {
    livewire(ListItems::class)
        ->assertTableFilterExists('brand')
        ->assertTableFilterExists('category')
        ->assertTableFilterExists('is_published')
        ->assertTableFilterExists('is_new')
        ->assertTableFilterExists('is_best_seller')
        ->assertTableFilterExists('trashed')
        ->assertTableActionExists('edit')
        ->assertTableActionExists('restore')
        ->assertTableActionExists('Duplicate')
        ->assertTableBulkActionExists('delete')
        ->assertTableBulkActionExists('restore')
        ->assertTableBulkActionExists('published')
        ->assertTableBulkActionExists('unpublished');
});

it('lists a product that has no picture', function (): void {
    livewire(ListItems::class)->assertOk()->assertCanSeeTableRecords([$this->online]);
});

it('does not count deleted products in the quick tabs', function (): void {
    Item::factory()->create(['is_published' => false])->delete();

    $tabs = livewire(ListItems::class)->instance()->getTabs();

    expect($tabs['hors-ligne']->getBadge())->toBe(1)
        ->and($tabs['en-ligne']->getBadge())->toBe(2);
});

it('labels the duplicate action in French', function (): void {
    livewire(ListItems::class)->assertSee('Dupliquer');
});

it('labels the publication bulk actions in French', function (): void {
    $table = livewire(ListItems::class)->instance()->getTable();

    expect($table->getBulkAction('published')->getLabel())->toBe('Mettre en ligne')
        ->and($table->getBulkAction('unpublished')->getLabel())->toBe('Retirer du site');
});
