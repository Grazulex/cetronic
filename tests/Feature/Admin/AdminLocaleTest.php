<?php

declare(strict_types=1);

use App\Filament\Resources\ItemResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Mail\ActifUser;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

it('runs the admin in French', function (): void {
    loginAsAdmin();

    $this->get(ItemResource::getUrl())->assertOk()->assertSee('Rechercher');
});

it('keeps the customer language for the activation e-mail sent from the admin', function (): void {
    Mail::fake();
    loginAsAdmin();
    $customer = User::factory()->create(['role' => 'customer', 'is_actif' => false, 'language' => 'nl']);

    livewire(EditUser::class, ['record' => $customer->getRouteKey()])
        ->fillForm(['is_actif' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    Mail::assertSent(ActifUser::class, fn(ActifUser $mail): bool => 'nl' === $mail->locale);
});

it('stays in French on Livewire updates (filters, actions)', function (): void {
    $persistent = app(Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware::class)->getPersistentMiddleware();

    expect($persistent)->toContain(App\Http\Middleware\SetAdminLocale::class);
});
