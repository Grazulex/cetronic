<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'company',
        'firstname',
        'lastname',
        'vat',
        'street',
        'street_number',
        'street_other',
        'zip',
        'city',
        'country',
        'phone',
    ];

    protected $appends = [
        'full_name',
    ];

    protected function casts(): array
    {
        return [
            'type' => LocationTypeEnum::class,
            'country' => CountryEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['vat'].' - '.trim($attributes['company'].' '.$attributes['firstname'].' '.$attributes['lastname']).' - '.trim($attributes['street'].' '.$attributes['street_number']).', '.$attributes['zip'].' '.$attributes['city'].'. '.$attributes['country'],
            set: null
        );
    }
}
