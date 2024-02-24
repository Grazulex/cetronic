<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\CategoryTranslation;
use Str;

final class CategoryTranslationObserver
{
    public function creating(CategoryTranslation $categoryTranslation): void
    {
        $categoryTranslation->slug = Str::slug($categoryTranslation->name);
    }

    public function updating(CategoryTranslation $categoryTranslation): void
    {
        if ($categoryTranslation->isDirty('name')) {
            $categoryTranslation->slug = Str::slug($categoryTranslation->name);
        }
    }
}
