<?php

declare(strict_types=1);

use App\Enum\CartStatusEnum;
use App\Filament\Resources\CartResource\Pages\EditCart;
use App\Filament\Resources\CartResource\Pages\ListCarts;
use App\Models\Cart;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    loginAsAdmin();
    $customer = User::factory()->create(['role' => 'customer']);
    $this->open = Cart::create(['user_id' => $customer->id, 'status' => CartStatusEnum::OPEN->value]);
    $this->sold = Cart::create(['user_id' => $customer->id, 'status' => CartStatusEnum::SOLD->value]);
});

it('opens on the open carts, like before', function (): void {
    livewire(ListCarts::class)
        ->assertCanSeeTableRecords([$this->open])
        ->assertCanNotSeeTableRecords([$this->sold]);
});

it('filters carts with the quick tabs', function (string $tab, array $visible, array $hidden): void {
    livewire(ListCarts::class)
        ->set('activeTab', $tab)
        ->assertCanSeeTableRecords(array_map(fn(string $name) => $this->{$name}, $visible))
        ->assertCanNotSeeTableRecords(array_map(fn(string $name) => $this->{$name}, $hidden));
})->with([
    'tous' => ['tous', ['open', 'sold'], []],
    'ouverts' => ['ouverts', ['open'], ['sold']],
    'vendus' => ['vendus', ['sold'], ['open']],
]);

it('shows French headers and statuses', function (): void {
    livewire(ListCarts::class)
        ->set('activeTab', 'tous')
        ->assertSee(['Client', 'Statut', 'Créé le', 'Modifié le', 'Ouvert', 'Vendu']);
});

it('keeps every filter, action and bulk action', function (): void {
    livewire(ListCarts::class)
        ->assertTableFilterExists('open')
        ->assertTableFilterExists('close')
        ->assertTableActionExists('edit')
        ->assertTableBulkActionExists('delete');
});

it('shows the cart form in French', function (): void {
    livewire(EditCart::class, ['record' => $this->open->getRouteKey()])
        ->assertSee(['Client', 'Adresse de livraison', 'Adresse de facturation']);
});
