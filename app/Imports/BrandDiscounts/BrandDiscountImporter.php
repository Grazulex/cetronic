<?php

declare(strict_types=1);

namespace App\Imports\BrandDiscounts;

use App\DataObjects\BrandDiscounts\ImportReport;
use App\Enum\CartStatusEnum;
use App\Enum\PriceTypeEnum;
use App\Enum\UserRoleEnum;
use App\Exports\BrandDiscounts\BrandDiscountColumns;
use App\Jobs\ProcessUpdatePriceCart;
use App\Jobs\UpdateListDisabled;
use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\User;
use App\Models\UserBrand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reads the spreadsheet produced by BrandDiscountsExport and applies it to user_brands.
 *
 * - Only category rules are targeted (category_meta_id and category_meta_value NULL).
 * - An empty cell changes nothing; 0 is a value. Rules are never deleted.
 * - Rules are saved quietly: UserBrandObserver would queue one cart job per published
 *   item of the category. Exclusions and open carts are refreshed explicitly instead.
 */
final class BrandDiscountImporter
{
    private const BOUNDS = [
        'reduction' => [0, 100],
        'coefficient' => [0, 100],
        'addition_price' => [0, null],
        'franco' => [0, null],
        'shipping_price' => [0, null],
    ];

    private const TRUE_VALUES = ['oui', '1', 'x', 'true', 'yes'];

    private const FALSE_VALUES = ['non', '0', 'false', 'no'];

    /**
     * @return array<int, array<int, mixed>>
     */
    public function readFile(string $path): array
    {
        return Excel::toArray(new class () {}, $path)[0] ?? [];
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function analyse(Brand $brand, array $rows): ImportReport
    {
        return $this->plan($brand, $rows)['report'];
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function apply(Brand $brand, array $rows): ImportReport
    {
        ['report' => $report, 'changes' => $changes] = $this->plan($brand, $rows);

        if ($report->hasErrors()) {
            return $report;
        }

        $usersWithRuleChanges = DB::transaction(function () use ($brand, $changes): array {
            $touched = [];

            foreach ($changes as $change) {
                /** @var User $user */
                $user = $change['user'];

                if ([] !== $change['user_attributes']) {
                    $user->fill($change['user_attributes'])->save();
                }

                foreach ($change['rules'] as $ruleChange) {
                    $this->saveRule($brand, $user, $ruleChange['rule'], $ruleChange['category_id'], $ruleChange['attributes']);
                    $touched[$user->id] = $user;
                }
            }

            return $touched;
        });

        foreach ($usersWithRuleChanges as $user) {
            $this->refreshOpenCarts($brand, $user);
        }

        return $report;
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{report: ImportReport, changes: array<int, array<string, mixed>>}
     */
    private function plan(Brand $brand, array $rows): array
    {
        $errors = [];
        $columns = $this->mapColumns($brand, $rows[0] ?? [], $errors);

        if ([] !== $errors) {
            return ['report' => new ImportReport($errors), 'changes' => []];
        }

        $dataRows = array_slice($rows, 1, null, true);
        $users = $this->loadUsers($dataRows, $columns['user']['user_id']);
        $rules = $this->loadRules($brand, $users->keys()->all());

        $changes = [];
        $created = 0;
        $updated = 0;
        $usersUpdated = 0;
        $unchanged = 0;

        foreach ($dataRows as $index => $row) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            $excelRow = $index + 1;
            $rowErrors = [];
            $user = $this->resolveUser($brand, $row, $columns['user'], $users, $excelRow, $rowErrors);

            if (null === $user) {
                array_push($errors, ...$rowErrors);

                continue;
            }

            $userAttributes = $this->userChanges($user, $row, $columns['user'], $excelRow, $rowErrors);
            $ruleChanges = [];

            foreach ($columns['categories'] as $categoryId => $fieldColumns) {
                $existing = $rules->get("{$user->id}-{$categoryId}", collect());
                $attributes = $this->cellValues($row, $fieldColumns, 1 === $existing->count() ? $existing->first() : null, $excelRow, $rowErrors);

                if ([] === $attributes) {
                    continue;
                }

                if ($existing->count() > 1) {
                    $rowErrors[] = $this->error($excelRow, $this->headingOf($fieldColumns), 'Plusieurs remises existent pour ce client et cette catégorie : lancer « php artisan user-brands:dedupe » avant d\'importer.');

                    continue;
                }

                $rule = $existing->first();
                $diff = null === $rule ? $attributes : $this->ruleDiff($rule, $attributes);

                if ([] !== $diff) {
                    $ruleChanges[] = ['rule' => $rule, 'category_id' => $categoryId, 'attributes' => $diff];
                    null === $rule ? $created++ : $updated++;
                }
            }

            array_push($errors, ...$rowErrors);

            if ([] === $userAttributes && [] === $ruleChanges) {
                $unchanged++;

                continue;
            }

            if ([] !== $userAttributes) {
                $usersUpdated++;
            }

            $changes[] = ['user' => $user, 'user_attributes' => $userAttributes, 'rules' => $ruleChanges];
        }

        return [
            'report' => new ImportReport($errors, $created, $updated, $usersUpdated, $unchanged),
            'changes' => $changes,
        ];
    }

    /**
     * @param  array<int, mixed>  $headings
     * @param  array<int, array{row: int, column: string, message: string}>  $errors
     * @return array{user: array<string, int>, categories: array<int, array<string, array{index: int, heading: string}>>}
     */
    private function mapColumns(Brand $brand, array $headings, array &$errors): array
    {
        $userLabels = array_flip(BrandDiscountColumns::USER_FIELDS);
        $allowedCategories = BrandDiscountColumns::categoriesFor($brand)->pluck('name', 'id');
        $columns = ['user' => [], 'categories' => []];

        foreach ($headings as $index => $heading) {
            $heading = $this->clean($heading);

            if (null === $heading) {
                continue;
            }

            if (isset($userLabels[$heading])) {
                $columns['user'][$userLabels[$heading]] = $index;

                continue;
            }

            $parsed = BrandDiscountColumns::parse($heading);

            if (null === $parsed) {
                if (1 === preg_match('/\[\d+\]/', $heading)) {
                    $errors[] = $this->error(1, $heading, 'Colonne non reconnue : le libellé après [id] doit être l\'un de ' . implode(', ', BrandDiscountColumns::FIELDS) . '.');
                }

                continue;
            }

            if ( ! $allowedCategories->has($parsed['category_id'])) {
                $errors[] = $this->error(1, $heading, "Cette catégorie n'a pas d'article publié de la marque {$brand->name}.");

                continue;
            }

            $columns['categories'][$parsed['category_id']][$parsed['field']] = ['index' => $index, 'heading' => $heading];
        }

        foreach (['user_id', 'brand_id'] as $required) {
            if ( ! isset($columns['user'][$required])) {
                $errors[] = $this->error(1, BrandDiscountColumns::USER_FIELDS[$required], 'Colonne obligatoire absente.');
            }
        }

        return $columns;
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return Collection<int, User>
     */
    private function loadUsers(array $rows, int $idColumn): Collection
    {
        $ids = collect($rows)->map(fn(array $row): ?int => $this->integer($row[$idColumn] ?? null))->filter()->unique()->values();

        return User::query()
            ->where('role', UserRoleEnum::CUSTOMER->value)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  array<int, int>  $userIds
     * @return Collection<string, EloquentCollection<int, UserBrand>>
     */
    private function loadRules(Brand $brand, array $userIds): Collection
    {
        return UserBrand::query()
            ->where('brand_id', $brand->id)
            ->whereIn('user_id', $userIds)
            ->whereNotNull('category_id')
            ->whereNull('category_meta_id')
            ->whereNull('category_meta_value')
            ->get()
            ->groupBy(fn(UserBrand $rule): string => "{$rule->user_id}-{$rule->category_id}")
            ->toBase();
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array<string, int>  $userColumns
     * @param  Collection<int, User>  $users
     * @param  array<int, array{row: int, column: string, message: string}>  $errors
     */
    private function resolveUser(Brand $brand, array $row, array $userColumns, Collection $users, int $excelRow, array &$errors): ?User
    {
        if ($this->integer($row[$userColumns['brand_id']] ?? null) !== $brand->id) {
            $errors[] = $this->error($excelRow, BrandDiscountColumns::USER_FIELDS['brand_id'], "Ce fichier n'a pas été exporté pour la marque {$brand->name}.");

            return null;
        }

        $user = $users->get($this->integer($row[$userColumns['user_id']] ?? null) ?? 0);

        if (null === $user) {
            $errors[] = $this->error($excelRow, BrandDiscountColumns::USER_FIELDS['user_id'], 'Client inconnu.');
        }

        return $user;
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array<string, int>  $userColumns
     * @param  array<int, array{row: int, column: string, message: string}>  $errors
     * @return array<string, float|string>
     */
    private function userChanges(User $user, array $row, array $userColumns, int $excelRow, array &$errors): array
    {
        $changes = [];

        if (isset($userColumns['external_reference'])) {
            $reference = $this->text($row[$userColumns['external_reference']] ?? null);

            // Stored references may carry stray spaces: they are not a change.
            if (null !== $reference && $reference !== (string) $this->clean($user->external_reference)) {
                $changes['external_reference'] = $reference;
            }
        }

        foreach (['franco', 'shipping_price'] as $field) {
            if ( ! isset($userColumns[$field])) {
                continue;
            }

            $current = round((float) $user->{$field}, 2);
            $value = $this->numberCell($row[$userColumns[$field]] ?? null, $field, $current, $excelRow, BrandDiscountColumns::USER_FIELDS[$field], $errors);

            if (null !== $value && $value !== $current) {
                $changes[$field] = $value;
            }
        }

        return $changes;
    }

    /**
     * Non-empty, validated cells of one category, as user_brands attributes.
     *
     * @param  array<int, mixed>  $row
     * @param  array<string, array{index: int, heading: string}>  $fieldColumns
     * @param  array<int, array{row: int, column: string, message: string}>  $errors
     * @return array<string, bool|float|string>
     */
    private function cellValues(array $row, array $fieldColumns, ?UserBrand $rule, int $excelRow, array &$errors): array
    {
        $values = [];
        $current = null === $rule ? [] : $this->currentValues($rule);

        foreach ($fieldColumns as $field => $column) {
            $raw = $row[$column['index']] ?? null;

            if (null === $this->clean($raw)) {
                continue;
            }

            $value = match ($field) {
                'price_type' => $this->priceType($raw, $excelRow, $column['heading'], $errors),
                'not_show_promo', 'is_excluded' => $this->boolean($raw, $excelRow, $column['heading'], $errors),
                default => $this->numberCell($raw, $field, $current[$field] ?? null, $excelRow, $column['heading'], $errors),
            };

            if (null !== $value) {
                $values[$field] = $value;
            }
        }

        return $values;
    }

    /**
     * @param  array<string, bool|float|string>  $attributes
     * @return array<string, bool|float|string>
     */
    private function ruleDiff(UserBrand $rule, array $attributes): array
    {
        $current = $this->currentValues($rule);

        return array_filter($attributes, fn(bool|float|string $value, string $field): bool => $current[$field] !== $value, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Current rule values in the same shape as parsed cells.
     *
     * @return array<string, bool|float|string>
     */
    private function currentValues(UserBrand $rule): array
    {
        return [
            'reduction' => round((float) $rule->reduction, 2),
            'coefficient' => round((float) $rule->coefficient, 2),
            'addition_price' => round((float) $rule->addition_price, 2),
            'price_type' => $rule->price_type->value,
            'not_show_promo' => $rule->not_show_promo,
            'is_excluded' => $rule->is_excluded,
        ];
    }

    /**
     * @param  array<string, bool|float|string>  $attributes
     */
    private function saveRule(Brand $brand, User $user, ?UserBrand $rule, int $categoryId, array $attributes): void
    {
        $rule ??= new UserBrand([
            'user_id' => $user->id,
            'brand_id' => $brand->id,
            'category_id' => $categoryId,
            'reduction' => 0,
            'coefficient' => 0,
            'addition_price' => 0,
            'price_type' => PriceTypeEnum::DEFAULT,
            'not_show_promo' => false,
            'is_excluded' => false,
        ]);

        $rule->fill($attributes);
        $exclusionChanged = $rule->exists ? $rule->isDirty('is_excluded') : $rule->is_excluded;
        $rule->saveQuietly();

        if ($exclusionChanged) {
            app()->call([new UpdateListDisabled($rule), 'handle']);
        }
    }

    private function refreshOpenCarts(Brand $brand, User $user): void
    {
        $itemIds = CartItem::query()
            ->whereHas('cart', fn(Builder $query) => $query->where('user_id', $user->id)->where('status', CartStatusEnum::OPEN->value))
            ->whereHas('item', fn(Builder $query) => $query->where('brand_id', $brand->id))
            ->distinct()
            ->pluck('item_id');

        Item::query()->whereIn('id', $itemIds)->get()
            ->each(fn(Item $item) => ProcessUpdatePriceCart::dispatch(item: $item, user: $user));
    }

    /**
     * Parses a numeric cell. A value equal to the stored one ($current) is never rejected:
     * legacy data such as franco = -1 ("no free shipping") must not block an import.
     *
     * @param  array<int, array{row: int, column: string, message: string}>  $errors
     */
    private function numberCell(mixed $raw, string $field, ?float $current, int $excelRow, string $heading, array &$errors): ?float
    {
        if (null === $this->clean($raw)) {
            return null;
        }

        $normalised = is_string($raw) ? str_replace(',', '.', (string) $this->clean($raw)) : $raw;

        if ( ! is_numeric($normalised)) {
            $errors[] = $this->error($excelRow, $heading, 'Nombre attendu (valeur : ' . $this->clean($raw) . ').');

            return null;
        }

        $value = round((float) $normalised, 2);
        [$min, $max] = self::BOUNDS[$field];

        if ($value === $current) {
            return $value;
        }

        if ($value < $min || (null !== $max && $value > $max)) {
            $range = null === $max ? "supérieure ou égale à {$min}" : "entre {$min} et {$max}";
            $errors[] = $this->error($excelRow, $heading, "La valeur doit être {$range} (valeur : {$value}).");

            return null;
        }

        return $value;
    }

    /**
     * @param  array<int, array{row: int, column: string, message: string}>  $errors
     */
    private function priceType(mixed $raw, int $excelRow, string $heading, array &$errors): ?string
    {
        $value = mb_strtolower((string) $this->clean($raw));

        if (null === PriceTypeEnum::tryFrom($value)) {
            $allowed = implode(', ', array_map(fn(PriceTypeEnum $type): string => $type->value, PriceTypeEnum::cases()));
            $errors[] = $this->error($excelRow, $heading, "Type de prix inconnu « {$value} » (attendu : {$allowed}).");

            return null;
        }

        return $value;
    }

    /**
     * @param  array<int, array{row: int, column: string, message: string}>  $errors
     */
    private function boolean(mixed $raw, int $excelRow, string $heading, array &$errors): ?bool
    {
        $value = is_bool($raw) ? ($raw ? '1' : '0') : mb_strtolower((string) $this->clean($raw));

        if (in_array($value, self::TRUE_VALUES, true)) {
            return true;
        }

        if (in_array($value, self::FALSE_VALUES, true)) {
            return false;
        }

        $errors[] = $this->error($excelRow, $heading, "Valeur « {$value} » non reconnue (attendu : oui ou non).");

        return null;
    }

    /**
     * Cell as a string without surrounding spaces, or null when empty.
     * Regex instead of trim(): Pint rewrites trim() to mb_trim(), which does not exist on PHP 8.3.
     */
    private function clean(mixed $value): ?string
    {
        if (null === $value || is_array($value)) {
            return null;
        }

        $string = preg_replace('/^\s+|\s+$/u', '', is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        return null === $string || '' === $string ? null : $string;
    }

    private function text(mixed $value): ?string
    {
        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return $this->clean($value);
    }

    private function integer(mixed $value): ?int
    {
        $string = $this->clean($value);

        return null !== $string && 1 === preg_match('/^\d+(\.0+)?$/', $string) ? (int) $string : null;
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function isBlankRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (null !== $this->clean($cell)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, array{index: int, heading: string}>  $fieldColumns
     */
    private function headingOf(array $fieldColumns): string
    {
        return (string) (reset($fieldColumns)['heading'] ?? '');
    }

    /**
     * @return array{row: int, column: string, message: string}
     */
    private function error(int $row, string $column, string $message): array
    {
        return ['row' => $row, 'column' => $column, 'message' => $message];
    }
}
