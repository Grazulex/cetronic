<?php

declare(strict_types=1);

namespace App\Services;

use App\DataObjects\Stats\SalesFilters;
use App\Enum\SalesGroupByEnum;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Sales figures computed from order lines.
 *
 * A sale is any line of a non-deleted order, whatever its status: the shop
 * never moves orders out of "open". Amounts are excl. VAT and stored in cents
 * (price_paid × quantity); they are only converted to euros at the edges.
 */
final class SalesStatsService
{
    /**
     * @return array{revenue: float, quantity: int, orders: int, customers: int, average_order: float}
     */
    public function summary(SalesFilters $filters): array
    {
        $row = $this->baseQuery($filters)
            ->selectRaw('COALESCE(SUM(order_items.price_paid * order_items.quantity), 0) as revenue')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as quantity')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as orders')
            ->selectRaw('COUNT(DISTINCT orders.user_id) as customers')
            ->toBase()
            ->first();

        $revenue = $this->euros($row->revenue);
        $orders = (int) $row->orders;

        return [
            'revenue' => $revenue,
            'quantity' => (int) $row->quantity,
            'orders' => $orders,
            'customers' => (int) $row->customers,
            'average_order' => $orders > 0 ? round($revenue / $orders, 2) : 0.0,
        ];
    }

    /**
     * Revenue and quantity per month, empty months included.
     *
     * @return array<int, array{month: string, revenue: float, quantity: int}>
     */
    public function monthly(SalesFilters $filters): array
    {
        $month = $this->monthExpression();

        $rows = $this->baseQuery($filters)
            ->selectRaw("{$month} as month")
            ->selectRaw('SUM(order_items.price_paid * order_items.quantity) as revenue')
            ->selectRaw('SUM(order_items.quantity) as quantity')
            ->groupByRaw($month)
            ->toBase()
            ->get()
            ->keyBy('month');

        if ($rows->isEmpty() && (null === $filters->from || null === $filters->to)) {
            return [];
        }

        $start = ($filters->from ?? Carbon::parse($rows->keys()->min() . '-01'))->copy()->startOfMonth();
        $end = ($filters->to ?? Carbon::parse($rows->keys()->max() . '-01'))->copy()->startOfMonth();

        $months = [];
        for ($cursor = $start; $cursor->lte($end); $cursor->addMonth()) {
            $key = $cursor->format('Y-m');
            $row = $rows->get($key);
            $months[] = [
                'month' => $key,
                'revenue' => $this->euros($row->revenue ?? 0),
                'quantity' => (int) ($row->quantity ?? 0),
            ];
        }

        return $months;
    }

    /**
     * One row per product, brand or category. Each row exposes `id`, `label`,
     * `quantity`, `revenue` (in cents), `orders_count`, `customers_count`,
     * `products_count` and `last_sold_at`; product rows also expose `slug` (route key), `brand_name` and
     * `category_name`.
     *
     * @return Builder<OrderItem>
     */
    public function breakdownQuery(SalesFilters $filters, SalesGroupByEnum $groupBy): Builder
    {
        $query = $this->baseQuery($filters)
            ->selectRaw('SUM(order_items.quantity) as quantity')
            ->selectRaw('SUM(order_items.price_paid * order_items.quantity) as revenue')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as orders_count')
            ->selectRaw('COUNT(DISTINCT orders.user_id) as customers_count')
            ->selectRaw('COUNT(DISTINCT order_items.item_id) as products_count')
            ->selectRaw('MAX(orders.created_at) as last_sold_at');

        return match ($groupBy) {
            SalesGroupByEnum::ITEM => $query
                ->join('brands', 'brands.id', '=', 'items.brand_id')
                ->join('categories', 'categories.id', '=', 'items.category_id')
                ->addSelect([
                    'items.id as id',
                    'items.reference as label',
                    'items.slug as slug',
                    'brands.name as brand_name',
                    'categories.name as category_name',
                ])
                ->groupBy('items.id', 'items.reference', 'items.slug', 'brands.name', 'categories.name'),
            SalesGroupByEnum::BRAND => $query
                ->join('brands', 'brands.id', '=', 'items.brand_id')
                ->addSelect(['brands.id as id', 'brands.name as label'])
                ->groupBy('brands.id', 'brands.name'),
            SalesGroupByEnum::CATEGORY => $query
                ->join('categories', 'categories.id', '=', 'items.category_id')
                ->addSelect(['categories.id as id', 'categories.name as label'])
                ->groupBy('categories.id', 'categories.name'),
        };
    }

    /**
     * Rows expose id, name, role, revenue (euros), quantity and orders_count.
     *
     * @return Collection<int, mixed>
     */
    public function topCustomers(SalesFilters $filters, int $limit = 10): Collection
    {
        return $this->baseQuery($filters)
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select(['users.id', 'users.name', 'users.role'])
            ->selectRaw('SUM(order_items.price_paid * order_items.quantity) as revenue')
            ->selectRaw('SUM(order_items.quantity) as quantity')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as orders_count')
            ->groupBy('users.id', 'users.name', 'users.role')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(fn(object $row): object => (object) [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'role' => (string) $row->role,
                'revenue' => $this->euros($row->revenue),
                'quantity' => (int) $row->quantity,
                'orders_count' => (int) $row->orders_count,
            ]);
    }

    /**
     * Order lines of non-deleted orders, joined to their product (trashed
     * products included: the join bypasses the soft-delete scope).
     *
     * @return Builder<OrderItem>
     */
    private function baseQuery(SalesFilters $filters): Builder
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('items', 'items.id', '=', 'order_items.item_id')
            ->whereNull('orders.deleted_at')
            ->when($filters->from, fn(Builder $q, Carbon $from) => $q->where('orders.created_at', '>=', $from->copy()->startOfDay()))
            ->when($filters->to, fn(Builder $q, Carbon $to) => $q->where('orders.created_at', '<=', $to->copy()->endOfDay()))
            ->when($filters->brandIds, fn(Builder $q, array $ids) => $q->whereIn('items.brand_id', $ids))
            ->when($filters->categoryIds, fn(Builder $q, array $ids) => $q->whereIn('items.category_id', $ids))
            ->when($filters->itemId, fn(Builder $q, int $id) => $q->where('order_items.item_id', $id))
            ->when($filters->userId, fn(Builder $q, int $id) => $q->where('orders.user_id', $id))
            ->when($filters->agentId, fn(Builder $q, int $id) => $q->where('orders.agent_id', $id))
            ->when($filters->search, function (Builder $q, string $search): void {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $q->where(fn(Builder $q) => $q
                    ->where('items.reference', 'like', $like)
                    ->orWhere('order_items.name', 'like', $like));
            });
    }

    private function monthExpression(): string
    {
        return 'sqlite' === DB::connection()->getDriverName()
            ? "strftime('%Y-%m', orders.created_at)"
            : "DATE_FORMAT(orders.created_at, '%Y-%m')";
    }

    private function euros(mixed $cents): float
    {
        return round(((int) $cents) / 100, 2);
    }
}
