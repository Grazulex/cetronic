<?php

declare(strict_types=1);

namespace App\Enum;

enum SalesGroupByEnum: string
{
    case ITEM = 'item';
    case BRAND = 'brand';
    case CATEGORY = 'category';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case): array => [$case->value => $case->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::ITEM => 'Produit',
            self::BRAND => 'Marque',
            self::CATEGORY => 'Catégorie',
        };
    }
}
