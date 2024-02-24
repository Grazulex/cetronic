<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use Str;

final class CategoryObserver
{
    public function creating(Category $category): void
    {
        $category->slug = Str::slug($category->name);
    }

    public function updating(Category $category): void
    {
        if ($category->isDirty('name')) {
            $category->slug = Str::slug($category->name);
        }
    }
}
