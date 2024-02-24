<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            /*
            Schema::disableForeignKeyConstraints();
            $table->dropForeign('user_brands_user_id_foreign');
            $table->dropForeign('user_brands_brand_id_foreign');
            $table->dropForeign('user_brands_category_id_foreign');
            $table->dropUnique(['user_id', 'brand_id', 'category_id']);

            $table->unique(['user_id', 'brand_id', 'category_id', 'category_meta_id', 'category_meta_value'])->name('user_brands_unique');


            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();

            Schema::enableForeignKeyConstraints();
*/
        });
    }

    public function down(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->unique(['user_id', 'brand_id', 'category_id']);

        });
    }
};
