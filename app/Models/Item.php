<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Item extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use Prunable;
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'slug',
        'master_id',
        'description',
        'brand_id',
        'category_id',
        'is_new',
        'is_published',
        'price',
        'price_b2b',
        'price_promo',
        'price_special1',
        'price_special2',
        'price_special3',
        'price_fix',
        'sale_price',
        'multiple_quantity'
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_new' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'master_id');
    }

    public function metas(): HasMany
    {
        return $this->hasMany(ItemMeta::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Item::class, 'master_id', 'id');
    }

    public function disables(): HasMany
    {
        return $this->hasMany(UserDisable::class);
    }

    public function scopeEnable(Builder $query, ?User $user = null): Builder
    {
        if ( ! $user) {
            return $query->where('is_published', true);
        }

        return $query->whereDoesntHave('disables', fn ($query) => $query->where('user_id', $user->id)->where('is_enable', true))
            ->where('is_published', true);
    }
    public function scopeActive(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function getMicroDescriptionAttribute(): string
    {
        return  trim(str_replace("\r", ' ', str_replace("\n", ' ', $this->description)));
    }

    public function prunable()
    {
        return static::where('deleted_at', '<', now()->subDays(30));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function priceB2b(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function pricePromo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function priceSpecial1(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function priceSpecial2(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function priceSpecial3(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function salePrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function priceFix(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function getFirstMediaPathAttribute()
    {
        return file_exists($this->getFirstMediaPath())
            ? $this->getFirstMediaPath()
            : public_path('emptyImage.jpg');

    }
}
