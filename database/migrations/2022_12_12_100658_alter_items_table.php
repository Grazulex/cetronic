<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->integer('sale_price')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->removeColumn('sale_price');
        });
    }
};