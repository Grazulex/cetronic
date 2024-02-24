<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Brand;
use Str;

final class BrandObserver
{
    public function saving(Brand $brand): void
    {
        $brand->slug = Str::slug($brand->name);
    }

    public function updating(Brand $brand): void
    {
        if ($brand->isDirty('name')) {
            $brand->slug = Str::slug($brand->name);
        }
    }
}
