<?php

declare(strict_types=1);

namespace App\DataObjects\Stats;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class SalesFilters
{
    /**
     * @param  array<int, int>  $brandIds
     * @param  array<int, int>  $categoryIds
     */
    public function __construct(
        public readonly ?Carbon $from = null,
        public readonly ?Carbon $to = null,
        public readonly array $brandIds = [],
        public readonly array $categoryIds = [],
        public readonly ?string $search = null,
        public readonly ?int $userId = null,
        public readonly ?int $agentId = null,
        public readonly ?int $itemId = null,
    ) {}

    /**
     * Period shortcuts offered in the admin, keyed by their form value.
     *
     * @return array<string, string>
     */
    public static function periods(): array
    {
        return [
            'this_month' => 'Ce mois-ci',
            'this_year' => 'Cette année',
            'last_12_months' => '12 derniers mois',
            'last_year' => 'Année précédente',
            'all' => 'Depuis le début',
        ];
    }

    /**
     * Dates (Y-m-d) covered by a period shortcut; null means unbounded.
     *
     * @return array{0: ?string, 1: ?string}
     */
    public static function periodRange(string $period): array
    {
        $now = Carbon::now();

        [$from, $to] = match ($period) {
            'all' => [null, null],
            'this_month' => [$now->copy()->startOfMonth(), $now],
            'last_12_months' => [$now->copy()->subMonthsNoOverflow(11)->startOfMonth(), $now],
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [$now->copy()->startOfYear(), $now],
        };

        return [$from?->toDateString(), $to?->toDateString()];
    }

    /**
     * Builds the filters from a Livewire / Filament form state.
     *
     * @param  array<string, mixed>  $state
     */
    public static function fromArray(array $state): self
    {
        $search = Str::squish((string) ($state['search'] ?? ''));

        return new self(
            from: self::date($state['from'] ?? null),
            to: self::date($state['to'] ?? null),
            brandIds: self::ids($state['brands'] ?? []),
            categoryIds: self::ids($state['categories'] ?? []),
            search: '' === $search ? null : $search,
            userId: self::id($state['user_id'] ?? null),
            agentId: self::id($state['agent_id'] ?? null),
            itemId: self::id($state['item_id'] ?? null),
        );
    }

    private static function date(mixed $value): ?Carbon
    {
        return blank($value) ? null : Carbon::parse($value);
    }

    private static function id(mixed $value): ?int
    {
        return blank($value) ? null : (int) $value;
    }

    /**
     * @return array<int, int>
     */
    private static function ids(mixed $values): array
    {
        return array_values(array_map('intval', array_filter((array) $values, fn($value): bool => ! blank($value))));
    }
}
