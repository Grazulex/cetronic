<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Removes duplicated category rules in user_brands (same user, brand and category, no meta).
 * MySQL lets them through because the unique index includes category_meta_value, which is NULL.
 * Identical duplicates keep their lowest id; conflicting ones are only reported.
 * Rows are deleted with the query builder on purpose: UserBrandObserver would otherwise queue
 * one cart price job per published item of the category.
 */
final class DedupeUserBrandsCommand extends Command
{
    private const VALUE_COLUMNS = ['reduction', 'coefficient', 'addition_price', 'price_type', 'not_show_promo', 'is_excluded'];

    protected $signature = 'user-brands:dedupe {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Merge identical duplicated category rules in user_brands and report conflicting ones';

    public function handle(): int
    {
        $groups = DB::table('user_brands')
            ->select('user_id', 'brand_id', 'category_id')
            ->whereNotNull('category_id')
            ->whereNull('category_meta_id')
            ->whereNull('category_meta_value')
            ->groupBy('user_id', 'brand_id', 'category_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $merged = 0;
        $deleted = 0;
        $conflicts = [];

        foreach ($groups as $group) {
            $rows = $this->rulesOf($group);

            if ($rows->map(fn(object $row): string => $this->signature($row))->unique()->count() > 1) {
                $conflicts[] = $this->describe($group, $rows);

                continue;
            }

            $duplicateIds = $rows->pluck('id')->sort()->slice(1)->values();
            $merged++;
            $deleted += $duplicateIds->count();

            if ( ! $this->option('dry-run')) {
                DB::table('user_brands')->whereIn('id', $duplicateIds)->delete();
            }
        }

        if ([] !== $conflicts) {
            $this->warn('Conflicting duplicates (fix them by hand in the customer "Brands" tab):');
            $this->table(['user_id', 'email', 'brand', 'category', 'ids'], $conflicts);
        }

        $prefix = $this->option('dry-run') ? '[dry-run] ' : '';
        $this->info("{$prefix}{$merged} groups merged, {$deleted} rows deleted, " . count($conflicts) . ' conflicts.');

        return self::SUCCESS;
    }

    private function rulesOf(object $group): Collection
    {
        return DB::table('user_brands')
            ->where('user_id', $group->user_id)
            ->where('brand_id', $group->brand_id)
            ->where('category_id', $group->category_id)
            ->whereNull('category_meta_id')
            ->whereNull('category_meta_value')
            ->orderBy('id')
            ->get();
    }

    private function signature(object $row): string
    {
        return implode('|', array_map(fn(string $column): string => (string) $row->{$column}, self::VALUE_COLUMNS));
    }

    /**
     * @return array<int, int|string|null>
     */
    private function describe(object $group, Collection $rows): array
    {
        return [
            $group->user_id,
            DB::table('users')->where('id', $group->user_id)->value('email'),
            DB::table('brands')->where('id', $group->brand_id)->value('name'),
            DB::table('categories')->where('id', $group->category_id)->value('name'),
            $rows->pluck('id')->implode(', '),
        ];
    }
}
