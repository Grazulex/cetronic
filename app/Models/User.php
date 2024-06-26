<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enum\UserRoleEnum;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable implements FilamentUser, HasLocalePreference
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_actif',
        'is_blocked',
        'role',
        'franco',
        'shipping_price',
        'agent_id',
        'external_reference',
        'divers',
        'receive_cart_notification',
        'language',
        'logged_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_actif' => 'boolean',
            'is_blocked' => 'boolean',
            'receive_cart_notification' => 'boolean',
            'role' => UserRoleEnum::class,
            'logged_at' => 'datetime',
        ];
    }

    public function preferredLocale(): string
    {
        return $this->language;
    }

    public function canAccessFilament(): bool
    {
        return UserRoleEnum::ADMIN === $this->role || UserRoleEnum::AGENT === $this->role;
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(UserBrand::class)->with('brand')->with('category')->orderBy('brand_id');
    }

    public function disables(): HasMany
    {
        return $this->hasMany(UserDisable::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function can_connect(): bool
    {
        return $this->is_actif && ! $this->is_blocked;
    }

    protected function franco(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function shippingPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucwords($value),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => mb_strtolower($value),
            set: fn ($value) => mb_strtolower($value)
        );
    }
}
