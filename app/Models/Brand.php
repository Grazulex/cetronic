<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

final class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_published',
        'is_featured',
        'is_register',
        'is_upload_actif',
        'order_register',
        'picture',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_register' => 'boolean',
            'is_upload_actif' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(related: UserBrand::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(related: Item::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(related: BrandTranslation::class);
    }

    public function userBrand(): HasMany
    {
        return $this->hasMany(related: UserBrand::class);
    }

    public function getMicroDescriptionAttribute(): string
    {
        return  trim(str_replace(search: "\r", replace: ' ', subject: str_replace(search: "\n", replace: ' ', subject: $this->description)));
    }


    public function scopeEnabled(Builder $query, ?User $user = null, ?Category $category = null): Builder
    {
        if ( ! $user) {
            return $query->where('is_published', true);
        }

        return $query->whereDoesntHave(relation: 'userBrand', callback: function ($query) use ($user, $category): void {
            $query->where('user_id', $user->id)->where('is_excluded', true);
            if ($category) {
                $query->where('category_id', $category->id);
            }
        })->where('is_published', true);
    }
}
