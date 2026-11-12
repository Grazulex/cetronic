<?php

declare(strict_types=1);

use App\Enum\CountryEnum;
use App\Filament\Resources\UserResource\RelationManagers\LocationsRelationManager;
use App\Models\User;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Support\Facades\App;

use function Pest\Livewire\livewire;

it('allows Europe, the French overseas departments, Madagascar and Mauritius only', function (): void {
    $codes = array_map(fn(CountryEnum $country): string => $country->value, CountryEnum::allowed());

    expect($codes)->toHaveCount(42)
        ->toContain('BEL', 'FRA', 'NLD', 'LUX', 'DEU', 'GRC', 'CHE', 'GBR', 'NOR', 'REU', 'GLP', 'MTQ', 'GUF', 'MYT', 'MDG', 'MUS')
        ->not->toContain('AFG')
        ->not->toContain('ZWE')
        ->not->toContain('USA');
});

it('lists allowed countries by translated name', function (): void {
    App::setLocale('fr');
    $options = CountryEnum::allowedOptions();

    expect(array_values($options)[0])->toBe('Allemagne')
        ->and($options['BEL'])->toBe('Belgique')
        ->and($options)->toHaveCount(42);
});

it('shows translated allowed countries on the register page', function (): void {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('value="BEL"', false)
        ->assertSee(CountryEnum::BELGIUM->label())
        ->assertDontSee('value="AFG"', false)
        ->assertDontSee('AFGHANISTAN');
});

it('refuses a non allowed country on registration', function (): void {
    $this->post('/register', [
        'name' => 'Jane Doe', 'company' => 'ACME', 'email' => 'jane@example.com', 'phone' => '0470000000',
        'vat' => 'BE0123456789', 'street' => 'Main street', 'street_number' => '1', 'zip' => '1000',
        'city' => 'Brussels', 'country' => 'AFG', 'language' => 'fr', 'customer' => 'yes',
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasErrors('country');
});

it('refuses a non allowed country on a customer address', function (): void {
    loginAsUser();

    $this->post(route('user_location.create'), [
        'type' => 'shipping', 'company' => 'ACME', 'firstname' => 'Jane', 'lastname' => 'Doe', 'vat' => 'BE0123456789',
        'street' => 'Main street', 'zip' => '1000', 'city' => 'Brussels', 'country' => 'USA', 'phone' => '0470000000',
    ])->assertSessionHasErrors('country');
});

it('shows translated allowed countries on the customer address form', function (): void {
    loginAsUser();

    $this->get(route('user_location.add'))
        ->assertOk()
        ->assertSee('value="MDG"', false)
        ->assertDontSee('value="ZWE"', false);
});

it('refuses a non allowed country in the admin address form', function (string $country, bool $accepted): void {
    loginAsAdmin();
    $customer = User::factory()->create(['role' => 'customer']);

    $component = livewire(LocationsRelationManager::class, ['ownerRecord' => $customer])
        ->callTableAction(CreateAction::class, data: [
            'type' => 'invoice', 'street' => 'Main street', 'zip' => '1000', 'city' => 'Brussels', 'country' => $country,
        ]);

    $accepted ? $component->assertHasNoTableActionErrors() : $component->assertHasTableActionErrors(['country']);
    expect($customer->locations()->where('country', $country)->exists())->toBe($accepted);
})->with([
    'Belgium' => ['BEL', true],
    'Afghanistan' => ['AFG', false],
]);
