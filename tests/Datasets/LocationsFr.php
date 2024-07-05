<?php

declare(strict_types=1);

use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use App\Models\Location;
use App\Models\User;

dataset(
    'locations_fr',
    [
        [fn() => Location::factory()->create(
            [
                'user_id' => User::factory()->create(['email' => 'france1@test.com', 'is_actif' => true])->id,
                'country' => CountryEnum::FRANCE,
                'type' => LocationTypeEnum::INVOICE],
        ),'country' => CountryEnum::FRANCE],
        [fn() => Location::factory()->create(
            [
                'user_id' => User::factory()->create(['email' => 'france2@test.com', 'is_actif' => true])->id,
                'country' => CountryEnum::FRANCE,
                'type' => LocationTypeEnum::INVOICE],
        ),'country' => CountryEnum::FRANCE],
        [fn() => Location::factory()->create(
            [
                'user_id' => User::factory()->create(['email' => 'france3@test.com', 'is_actif' => true])->id,
                'country' => CountryEnum::FRANCE,
                'type' => LocationTypeEnum::INVOICE],
        ),'country' => CountryEnum::FRANCE],
    ],
);
