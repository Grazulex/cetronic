<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\CartStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'cookie',
        'comment',
        'shipping_location_id',
        'invoice_location_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => CartStatusEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(related: CartItem::class);
    }

    public function shippingLocation(): BelongsTo
    {
        return $this->belongsTo(related: Location::class, foreignKey: 'shipping_location_id');
    }

    public function invoiceLocation(): BelongsTo
    {
        return $this->belongsTo(related: Location::class, foreignKey: 'invoice_location_id');
    }
}
