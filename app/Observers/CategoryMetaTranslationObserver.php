<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\CategoryMetaTranslation;
use Str;

final class CategoryMetaTranslationObserver
{
    public function creating(CategoryMetaTranslation $categoryMetaTranslation): void
    {
        $categoryMetaTranslation->slug = Str::slug($categoryMetaTranslation->name);
    }

    public function updating(CategoryMetaTranslation $categoryMetaTranslation): void
    {
        if ($categoryMetaTranslation->isDirty('name')) {
            $categoryMetaTranslation->slug = Str::slug($categoryMetaTranslation->name);
        }
    }
}
