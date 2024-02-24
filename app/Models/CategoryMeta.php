<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CategoryMeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'is_color',
        'is_meta',
        'is_export',
        'is_variant',
        'is_choice'
    ];

    protected $casts = [
        'is_color' => 'boolean',
        'is_meta' => 'boolean',
        'is_export' => 'boolean',
        'is_variant' => 'boolean',
        'is_choice' => 'boolean'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(related: Category::class);
    }

    public function types(): HasMany
    {
        return $this->hasMany(related: ItemMeta::class, foreignKey: 'meta_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(related: CategoryMetaTranslation::class);
    }
}
