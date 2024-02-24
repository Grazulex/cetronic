<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->renameColumn('price_special_1', 'price_special1');
        });
        Schema::table('items', function (Blueprint $table): void {
            $table->renameColumn('price_special_2', 'price_special2');
        });
        Schema::table('items', function (Blueprint $table): void {
            $table->renameColumn('price_special_3', 'price_special3');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->renameColumn('price_special1', 'price_special_1');
            $table->renameColumn('price_special2', 'price_special_2');
            $table->renameColumn('price_special3', 'price_special_3');
        });
    }
};
