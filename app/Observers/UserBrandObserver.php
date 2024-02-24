<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\ProcessUpdatePriceCart;
use App\Jobs\UpdateListDisabled;
use App\Models\UserBrand;

final class UserBrandObserver
{
    public function created(UserBrand $userBrand): void
    {
        if ($userBrand->is_excluded) {
            UpdateListDisabled::dispatch($userBrand);
        }
        foreach ($userBrand->category->items()->where('is_published', true)->get() as $item) {
            ProcessUpdatePriceCart::dispatch(item: $item, user: $userBrand->user);
        }
    }

    public function updated(UserBrand $userBrand): void
    {
        if ($userBrand->isDirty('is_excluded')) {
            UpdateListDisabled::dispatch($userBrand);
        }
        foreach ($userBrand->category->items()->where('is_published', true)->get() as $item) {
            ProcessUpdatePriceCart::dispatch(item: $item, user: $userBrand->user);
        }
    }

    public function deleting(UserBrand $userBrand): void
    {
        foreach ($userBrand->category->items()->where('is_published', true)->get() as $item) {
            ProcessUpdatePriceCart::dispatch(item: $item, user: $userBrand->user);
        }
    }
}
