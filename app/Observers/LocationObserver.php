<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enum\LocationTypeEnum;
use App\Models\Location;

final class LocationObserver
{
    public function created(Location $location): void
    {
        if (1 === $location->user->locations->count()) {
            $newLocation = $location->replicate();
            if (LocationTypeEnum::INVOICE === $location->type) {
                $newLocation->type = LocationTypeEnum::SHIPPING->value;
            } else {
                $newLocation->type = LocationTypeEnum::INVOICE->value;
            }
            $newLocation->saveQuietly();
        }
    }
}
