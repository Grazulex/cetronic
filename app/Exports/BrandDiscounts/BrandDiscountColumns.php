<?php

declare(strict_types=1);

namespace App\Exports\BrandDiscounts;

use App\Enum\UserRoleEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Single source of truth for the brand discounts spreadsheet layout,
 * shared by the export and the import so both always agree.
 */
final class BrandDiscountColumns
{
    /** Per-category columns: user_brands attribute => heading label. */
    public const FIELDS = [
        'reduction' => 'Réduction %',
        'coefficient' => 'Coefficient',
        'addition_price' => 'Ajout €',
        'price_type' => 'Type de prix',
        'not_show_promo' => 'Masquer promo',
        'is_excluded' => 'Exclu',
    ];

    /** Fixed leading columns: key => heading label. */
    public const USER_FIELDS = [
        'user_id' => 'ID client',
        'brand_id' => 'ID marque',
        'name' => 'Nom',
        'email' => 'Email',
        'external_reference' => 'Référence externe',
        'franco' => 'Franco',
        'shipping_price' => 'Frais de port',
    ];

    public const TRUE_LABEL = 'oui';

    public const FALSE_LABEL = 'non';

    /**
     * Categories holding at least one published item of the brand.
     *
     * @return Collection<int, Category>
     */
    public static function categoriesFor(Brand $brand): Collection
    {
        return Category::query()
            ->whereHas('items', fn(Builder $query) => $query->where('brand_id', $brand->id)->where('is_published', true))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Builder<User>
     */
    public static function customers(): Builder
    {
        return User::query()
            ->where('role', UserRoleEnum::CUSTOMER->value)
            ->where('is_actif', true)
            ->orderBy('name');
    }

    public static function heading(Category $category, string $field): string
    {
        return "{$category->name} [{$category->id}] " . self::FIELDS[$field];
    }

    /**
     * @return array{category_id: int, field: string}|null
     */
    public static function parse(string $heading): ?array
    {
        if (1 !== preg_match('/\[(\d+)\]\s*([^\[\]]+?)\s*$/u', $heading, $matches)) {
            return null;
        }

        $field = array_search($matches[2], self::FIELDS, true);

        return false === $field ? null : ['category_id' => (int) $matches[1], 'field' => $field];
    }

    public static function formatBool(bool $value): string
    {
        return $value ? self::TRUE_LABEL : self::FALSE_LABEL;
    }
}
