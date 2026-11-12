<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Production already has the 4-column unique index (user, brand, category, meta value),
 * applied by hand. Fresh installs and the test database still get the 3-column one from
 * the 2022 migrations, which forbids a category rule and a meta rule on the same trio.
 * This migration only acts when the old 3-column index is found, so it is a no-op in prod.
 */
return new class () extends Migration {
    private const OLD_INDEX = 'user_brands_user_id_brand_id_category_id_unique';

    private const NEW_INDEX = 'user_brands_rule_unique';

    public function up(): void
    {
        $old = collect(Schema::getIndexes('user_brands'))->firstWhere('name', self::OLD_INDEX);

        if (null === $old || ['user_id', 'brand_id', 'category_id'] !== $old['columns']) {
            return;
        }

        // Create the new index first: MySQL refuses to drop an index backing the user_id
        // foreign key until another index starting with user_id exists.
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->unique(['user_id', 'brand_id', 'category_id', 'category_meta_value'], self::NEW_INDEX);
        });

        Schema::table('user_brands', function (Blueprint $table): void {
            $table->dropUnique(self::OLD_INDEX);
        });
    }

    public function down(): void
    {
        // Intentionally left empty: restoring the 3-column index would fail as soon as
        // a category rule and a meta rule share the same user, brand and category.
    }
};
