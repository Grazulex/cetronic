<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'item_id',
        'quantity',
        'price_old',
        'price',
        'price_promo',
        'variant'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(related: Item::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(related: Cart::class);
    }

    protected function priceOld(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function price(): Attribute
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
}
