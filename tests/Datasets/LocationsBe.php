<?php

declare(strict_types=1);

use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use App\Models\Location;
use App\Models\User;

dataset(
    'locations_be',
    [
        [fn () => Location::factory()->create(
            [
                'user_id' => User::factory()->create(['email'=>'belgium1@test.com', 'is_actif'=>true, 'shipping_price' => 10, 'franco' => 0])->id,
                'country' => CountryEnum::BELGIUM,
                'type' => LocationTypeEnum::INVOICE],
        ), 'country' => CountryEnum::BELGIUM],
        [fn () => Location::factory()->create(
            [
                'user_id' => User::factory()->create(['email'=>'belgium2@test.com', 'is_actif'=>true, 'shipping_price' => 0, 'franco' => 500])->id,
                'country' => CountryEnum::BELGIUM,
                'type' => LocationTypeEnum::INVOICE],
        ), 'country' => CountryEnum::BELGIUM],
        [fn () => Location::factory()->create(
            [
                'user_id' => User::factory()->create(['email'=>'belgium3@test.com', 'is_actif'=>true, 'shipping_price' => 0, 'franco' => 0])->id,
                'country' => CountryEnum::BELGIUM,
                'type' => LocationTypeEnum::INVOICE],
        ), 'country' => CountryEnum::BELGIUM],
    ]
);
