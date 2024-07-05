<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ItemMeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_id',
        'item_id',
        'value',
    ];

    protected $with = [
        'item',
    ];

    protected $appends = [
        'color',
    ];

    public static function getDisplayedAttributes(): array
    {
        return [
            'meta_id',
            'meta_name',
            'is_color',
            'value',
        ];
    }

    public static function getSortableAttributes(): array
    {
        return [
            'value',
        ];
    }

    public function meta(): BelongsTo
    {
        return $this->belongsTo(CategoryMeta::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    protected function color(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value,
            set: fn($value) => $value,
        );
    }
}
