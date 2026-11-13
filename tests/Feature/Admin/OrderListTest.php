<?php

declare(strict_types=1);

use App\Enum\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use Illuminate\Support\Carbon;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Carbon::setTestNow('2026-11-13 10:00:00');
    loginAsAdmin();

    $this->open = Order::factory()->create(['status' => OrderStatusEnum::OPEN->value, 'created_at' => '2026-11-02']);
    $this->preparing = Order::factory()->create(['status' => OrderStatusEnum::IN_PREPARATION->value, 'created_at' => '2026-11-05']);
    $this->shipped = Order::factory()->create(['status' => OrderStatusEnum::SHIPPED->value, 'created_at' => '2026-10-20']);
});

afterEach(fn() => Carbon::setTestNow());

it('filters orders with the quick tabs', function (string $tab, array $visible, array $hidden): void {
    livewire(ListOrders::class)
        ->set('activeTab', $tab)
        ->assertCanSeeTableRecords(array_map(fn(string $name) => $this->{$name}, $visible))
        ->assertCanNotSeeTableRecords(array_map(fn(string $name) => $this->{$name}, $hidden));
})->with([
    'toutes' => ['toutes', ['open', 'preparing', 'shipped'], []],
    'ce mois' => ['ce-mois', ['open', 'preparing'], ['shipped']],
    'ouvertes' => ['ouvertes', ['open'], ['preparing', 'shipped']],
    'en préparation' => ['en-preparation', ['preparing'], ['open', 'shipped']],
    'expédiées' => ['expediees', ['shipped'], ['open', 'preparing']],
]);

it('shows French headers and statuses', function (): void {
    livewire(ListOrders::class)
        ->assertSee(['Date', 'Référence', 'Client', 'Statut', 'Total HT', 'Articles', 'Ouverte', 'En préparation', 'Expédiée']);
});

it('keeps every filter, action and bulk action', function (): void {
    livewire(ListOrders::class)
        ->assertTableFilterExists('trashed')
        ->assertTableFilterExists('open')
        ->assertTableFilterExists('in preparation')
        ->assertTableFilterExists('shipped')
        ->assertTableActionExists('edit')
        ->assertTableActionExists('exportPDF')
        ->assertTableBulkActionExists('delete')
        ->assertTableBulkActionExists('forceDelete')
        ->assertTableBulkActionExists('shippend');
});

it('shows the order form in French and still saves the tracking', function (): void {
    livewire(EditOrder::class, ['record' => $this->open->getRouteKey()])
        ->assertSee(['Référence', 'Statut', 'Suivi', 'Numéro de suivi', 'Lien de suivi'])
        ->fillForm(['tracking_number' => 'BE123'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->open->fresh()->tracking_number)->toBe('BE123');
});
