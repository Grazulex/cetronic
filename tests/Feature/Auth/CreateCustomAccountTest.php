<?php

declare(strict_types=1);

use App\Enum\LocationTypeEnum;
use App\Enum\UserRoleEnum;
use App\Models\Location;
use App\Models\User;

it('can go to register page', function (): void {
    $this->get(uri: route('register'))
        ->assertStatus(200)
        ->assertSee(__('user.register'))
        ->assertSee(__('user.name'))
        ->assertSee(__('user.company'))
        ->assertSee(__('user.email'))
        ->assertSee(__('user.phone'))
        ->assertSee(__('user.vat'))
        ->assertSee(__('user.street'))
        ->assertSee(__('user.street_number'))
        ->assertSee(__('user.zip'))
        ->assertSee(__('user.city'))
        ->assertSee(__('user.country'))
        ->assertSee(__('user.language'))
        ->assertSee(__('user.customer'))
        ->assertSee(__('user.yes'))
        ->assertSee(__('user.no'))
        ->assertSee(__('user.brands'))
        ->assertSee(__('user.password'))
        ->assertSee(__('user.confirm'));
});

it('can create a new account', function (): void {
    $this->post(
        uri: '/register',
        data: [
            'name' => $name = fake()->name,
            'company' => $company = fake()->company,
            'email' => $email = fake()->email,
            'phone' => $phone = fake()->phoneNumber,
            'vat' => $vat = fake()->iban,
            'street' => $street = fake()->streetName,
            'street_number' => $street_number = fake()->buildingNumber,
            'zip' => $zip = fake()->postcode,
            'city' => $city = fake()->city,
            'country' => $country = fake()->countryISOAlpha3,
            'language' => $language = fake()->languageCode,
            'customer' => 'yes',
            'brands' => ['brand1', 'brand2'],
            'password' => 'password',
            'password_confirmation' => 'password',
        ],
    )
        ->assertRedirect(route('user_thanks'))
        ->assertSessionHas('status', __('user.auth.registered.waiting'));

    expect(User::first()->name)->toBe($name);
    expect(User::first()->email)->toBe($email);
    expect(User::first()->is_actif)->toBeFalse();
    expect(User::first()->divers)->toBe('Registration infos. Is a old customer: yes. Interested in: brand1,brand2');
    expect(User::first()->role)->toBe(UserRoleEnum::CUSTOMER);
    expect(User::first()->is_actif)->toBeFalse();
    expect(User::first()->language)->toBe($language);
    expect(User::first()->is_blocked)->toBeFalse();
    expect(User::first()->external_reference)->toBeNull();

    expect(User::first()->locations->count())->toBe(2);

    expect(Location::first()->lastname)->toBe($name);
    expect(Location::first()->company)->toBe($company);
    expect(Location::first()->type)->toBe(LocationTypeEnum::INVOICE);
    expect(Location::first()->vat)->toBe($vat);
    expect(Location::first()->phone)->toBe($phone);
    expect(Location::first()->street)->toBe($street);
    expect(Location::first()->street_number)->toBe($street_number);
    expect(Location::first()->zip)->toBe($zip);
    expect(Location::first()->city)->toBe($city);
    expect(Location::first()->country->value)->toBe($country);

    expect(Location::skip(1)->first()->lastname)->toBe($name);
    expect(Location::skip(1)->first()->company)->toBe($company);
    expect(Location::skip(1)->first()->type)->toBe(LocationTypeEnum::SHIPPING);
    expect(Location::skip(1)->first()->vat)->toBe($vat);
    expect(Location::skip(1)->first()->phone)->toBe($phone);
    expect(Location::skip(1)->first()->street)->toBe($street);
    expect(Location::skip(1)->first()->street_number)->toBe($street_number);
    expect(Location::skip(1)->first()->zip)->toBe($zip);
    expect(Location::skip(1)->first()->city)->toBe($city);
    expect(Location::skip(1)->first()->country->value)->toBe($country);
});

it('can not create a account without email', function (): void {
    $this->post(
        uri: '/register',
        data: [
            'name' => fake()->name,
            'company' => fake()->company,
            'email' => '',
            'phone' => fake()->phoneNumber,
            'vat' => fake()->iban,
            'street' => fake()->streetName,
            'street_number' => fake()->buildingNumber,
            'zip' => fake()->postcode,
            'city' => fake()->city,
            'country' => fake()->countryISOAlpha3,
            'language' => fake()->languageCode,
            'customer' => 'yes',
            'brands' => ['brand1', 'brand2'],
            'password' => 'password',
            'password_confirmation' => 'password',
        ],
    )
        ->assertSessionHasErrors('email');
});

it('can not create a account without vat', function (): void {
    $this->post(
        uri: '/register',
        data: [
            'name' => fake()->name,
            'company' => fake()->company,
            'email' => fake()->email,
            'phone' => fake()->phoneNumber,
            'vat' => '',
            'street' => fake()->streetName,
            'street_number' => fake()->buildingNumber,
            'zip' => fake()->postcode,
            'city' => fake()->city,
            'country' => fake()->countryISOAlpha3,
            'language' => fake()->languageCode,
            'customer' => 'yes',
            'brands' => ['brand1', 'brand2'],
            'password' => 'password',
            'password_confirmation' => 'password',
        ],
    )
        ->assertSessionHasErrors('vat');
});
