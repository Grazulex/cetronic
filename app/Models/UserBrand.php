<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\PriceTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_id',
        'category_id',
        'category_meta_id',
        'category_meta_value',
        'reduction',
        'coefficient',
        'addition_price',
        'price_type',
        'not_show_promo',
        'is_excluded',
    ];

    protected $casts = [
        'price_type' => PriceTypeEnum::class,
        'not_show_promo' => 'boolean',
        'is_excluded' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function category_meta(): BelongsTo
    {
        return $this->belongsTo(CategoryMeta::class);
    }

    protected function reduction(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => $value * 100,
        );
    }

    protected function coefficient(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => $value * 100,
        );
    }

    protected function additionPrice(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => $value * 100,
        );
    }
}
