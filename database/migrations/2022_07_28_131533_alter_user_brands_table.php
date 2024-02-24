<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->unique(['user_id', 'brand_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'brand_id', 'category_id']);
        });
    }
};
