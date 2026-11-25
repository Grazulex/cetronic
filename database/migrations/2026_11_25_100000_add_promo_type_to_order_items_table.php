<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Kind of promotion an order line was sold at ("fixed" for price_fix,
 * "personal" for price_promo), shown on the order PDF. Existing lines are
 * filled in from what the prices tell: a promo price was kept in price_show,
 * a fixed price is the item's price_fix paid as is.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function ($table): void {
            $table->string('promo_type')->nullable()->after('price_show');
        });

        DB::table('order_items')
            ->where('price_show', '>', 0)
            ->update(['promo_type' => 'personal']);

        DB::table('order_items')
            ->whereNull('promo_type')
            ->whereExists(fn(Builder $query) => $query->selectRaw('1')
                ->from('items')
                ->whereColumn('items.id', 'order_items.item_id')
                ->where('items.price_fix', '>', 0)
                ->whereColumn('items.price_fix', 'order_items.price_paid'))
            ->update(['promo_type' => 'fixed']);
    }

    public function down(): void
    {
        Schema::table('order_items', function ($table): void {
            $table->dropColumn('promo_type');
        });
    }
};
