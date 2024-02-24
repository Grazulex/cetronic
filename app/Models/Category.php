<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_published',
        'is_featured',
        'is_export',
        'picture',
        'order',
        'show_picture_variant'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_export' => 'boolean',
        'show_picture_variant' => 'boolean'
    ];

    public function metas(): HasMany
    {
        return $this->hasMany(related: CategoryMeta::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(related: Item::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(related: CategoryTranslation::class);
    }

    public function userBrand(): HasMany
    {
        return $this->hasMany(related: UserBrand::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
