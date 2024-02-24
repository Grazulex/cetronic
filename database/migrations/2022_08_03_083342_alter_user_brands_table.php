<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->removeColumn('show_promo');
            $table->boolean('not_show_promo')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->removeColumn('not_show_promo');
            $table->boolean('show_promo')->default(false);
        });
    }
};
