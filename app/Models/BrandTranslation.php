<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\TranslationLanguagesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BrandTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'description',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'locale' => TranslationLanguagesEnum::class,
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(related: Brand::class);
    }
}
