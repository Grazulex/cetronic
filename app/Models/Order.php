<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\CountryEnum;
use App\Enum\OrderStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'agent_id',
        'status',
        'reference',
        'tracking_number',
        'tracking_url',
        'shipping_company',
        'shipping_name',
        'shipping_street',
        'shipping_street_number',
        'shipping_street_other',
        'shipping_city',
        'shipping_zip',
        'shipping_country',
        'shipping_phone',
        'shipping_email',
        'total_price',
        'total_price_with_tax',
        'total_tax',
        'total_shipping',
        'total_shipping_with_tax',
        'total_shipping_tax',
        'total_products',
        'total_products_with_tax',
        'total_products_tax',
        'discount',
        'invoice_email',
        'invoice_company',
        'invoice_name',
        'invoice_street',
        'invoice_street_number',
        'invoice_street_other',
        'invoice_city',
        'invoice_zip',
        'invoice_country',
        'invoice_vat',
        'comment',
        'franco'

    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'country' => CountryEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->select([
            'order_items.*',
            'items.reference as item_reference',
            'brands.name as brand_name',
        ])
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('brands', 'items.brand_id', '=', 'brands.id')
            ->orderBy('categories.order')
            ->orderBy('brands.name')
            ->orderBy('items.reference')
        ;
    }

    protected function franco(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalPriceWithTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalShipping(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalShippingWithTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalShippingTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalProducts(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalProductsWithTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function totalProductsTax(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function discount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }
}
