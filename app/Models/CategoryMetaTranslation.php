<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\TranslationLanguagesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CategoryMetaTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_meta_id',
        'name',
        'slug',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'locale' => TranslationLanguagesEnum::class,
        ];
    }

    public function category_meta(): BelongsTo
    {
        return $this->belongsTo(CategoryMeta::class);
    }
}
