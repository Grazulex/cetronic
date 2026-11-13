<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->admin = loginAsAdmin();
    $this->active = User::factory()->create(['role' => 'customer', 'is_actif' => true]);
    $this->waiting = User::factory()->create(['role' => 'customer', 'is_actif' => false]);
    $this->agent = User::factory()->create(['role' => 'agent', 'is_actif' => true]);
});

it('filters users with the quick tabs', function (string $tab, array $visible, array $hidden): void {
    livewire(ListUsers::class)
        ->set('activeTab', $tab)
        ->assertCanSeeTableRecords(array_map(fn(string $name) => $this->{$name}, $visible))
        ->assertCanNotSeeTableRecords(array_map(fn(string $name) => $this->{$name}, $hidden));
})->with([
    'tous' => ['tous', ['active', 'waiting', 'agent', 'admin'], []],
    'clients' => ['clients', ['active', 'waiting'], ['agent', 'admin']],
    'agents' => ['agents', ['agent'], ['active', 'waiting', 'admin']],
    'administrateurs' => ['administrateurs', ['admin'], ['active', 'waiting', 'agent']],
    'à activer' => ['a-activer', ['waiting'], ['active', 'agent', 'admin']],
]);

it('shows only the useful columns by default, with French headers', function (): void {
    livewire(ListUsers::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('orders_count')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at')
        ->assertCanNotRenderTableColumn('logged_at')
        ->assertCanNotRenderTableColumn('email')
        ->assertSee(['Réf. externe', 'Nom', 'Type de compte', 'Type de client', 'Actif', 'Commandes', 'Client', 'Agent']);
});

it('keeps every filter, action and bulk action', function (): void {
    $component = livewire(ListUsers::class);

    foreach (['trashed', 'role', 'customer_type_id', 'agent_id', 'is_actif', 'is_blocked', 'language', 'invoice_country',
        'invoice_zip', 'invoice_city', 'has_orders', 'no_orders', 'franco_above_100', 'created_last_month', 'never_logged'] as $filter) {
        $component->assertTableFilterExists($filter);
    }

    $component
        ->assertTableActionExists('edit')
        ->assertTableActionExists('stats')
        ->assertTableActionExists('Login')
        ->assertTableActionExists('Print')
        ->assertTableActionExists('delete')
        ->assertTableActionExists('forceDelete')
        ->assertTableActionExists('restore')
        ->assertTableBulkActionExists('delete')
        ->assertTableBulkActionExists('resetPassword')
        ->assertTableBulkActionExists('forceDelete')
        ->assertTableBulkActionExists('restore');
});

it('shows the customer form in French and still saves it', function (): void {
    livewire(EditUser::class, ['record' => $this->active->getRouteKey()])
        ->assertSee(['Informations', 'Livraison', 'Activation', 'Remises par marque', 'Nom', 'Langue', 'Compte actif'])
        ->fillForm(['name' => 'Bijouterie Dupont'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->active->fresh()->name)->toBe('Bijouterie Dupont');
});

it('keeps only edit and statistics as visible row buttons, the rest in a menu', function (): void {
    $actions = livewire(ListUsers::class)->instance()->getTable()->getActions();

    $visible = collect($actions)->reject(fn($action) => $action instanceof Filament\Tables\Actions\ActionGroup)
        ->map(fn($action) => $action->getName())->values()->all();

    $icons = collect($actions)->reject(fn($action) => $action instanceof Filament\Tables\Actions\ActionGroup)
        ->every(fn($action): bool => $action->isIconButton());

    expect($icons)->toBeTrue('row buttons are icons, so the row fits the screen')
        ->and($visible)->toBe(['edit', 'stats'])
        ->and(collect($actions)->filter(fn($action) => $action instanceof Filament\Tables\Actions\ActionGroup))->toHaveCount(1);
});

it('labels the Excel export in French', function (): void {
    livewire(ListUsers::class)->assertSee('Exporter en Excel')->assertDontSee('Export to XLSX');
});

it('shows the e-mail under the name and still finds a user by e-mail', function (): void {
    $this->active->update(['email' => 'dupont@bijouterie.be']);

    livewire(ListUsers::class)
        ->assertSee('dupont@bijouterie.be')
        ->searchTable('dupont@bijouterie')
        ->assertCanSeeTableRecords([$this->active])
        ->assertCanNotSeeTableRecords([$this->waiting, $this->agent]);
});
