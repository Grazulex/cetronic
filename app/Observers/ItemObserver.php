<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\ProcessAddItemCart;
use App\Jobs\ProcessRemoveItemCart;
use App\Jobs\ProcessUpdatePriceCart;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Str;

final class ItemObserver
{
    public function deleting(Item $item): void
    {
        ProcessRemoveItemCart::dispatch($item);
        if (Storage::exists('public/items/' . $item->id)) {
            Storage::deleteDirectory('public/items/' . $item->id);
        }
    }

    public function creating(Item $item): void
    {
        $item->slug = Str::slug($item->brand_id . '_' . $item->category_id . '-' . $item->reference);
    }

    public function updating(Item $item): void
    {
        if ($item->isDirty('brand_id') || $item->isDirty('reference')) {
            $item->slug = Str::slug($item->brand_id . '_' . $item->category_id . '-' . $item->reference);
        }

        if (
            $item->isDirty('price') ||
            $item->isDirty('price_b2b') ||
            $item->isDirty('price_promo') ||
            $item->isDirty('price_special') ||
            $item->isDirty('price_special2') ||
            $item->isDirty('price_special3')
        ) {
            ProcessUpdatePriceCart::dispatch($item);
        }

        if ($item->isDirty('is_published')) {
            if ( ! $item->is_published) {
                ProcessRemoveItemCart::dispatch($item);
            } else {
                ProcessAddItemCart::dispatch($item);
            }
        }
    }
}
