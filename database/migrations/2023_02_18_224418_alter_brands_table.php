<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table): void {
            $table->boolean('is_register')->default(true);
            $table->integer('order_register')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table): void {
            $table->removeColumn('order_register');
            $table->removeColumn('is_register');
        });
    }
};
