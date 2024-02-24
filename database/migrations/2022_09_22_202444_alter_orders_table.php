<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->removeColumn('shipping_name');
            $table->removeColumn('invoice_name');
            $table->string('shipping_firstname')->nullable();
            $table->string('invoice_firstname')->nullable();
            $table->string('shipping_lastname')->nullable();
            $table->string('invoice_lastname')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_name')->nullable();
            $table->string('invoice_name')->nullable();
            $table->removeColumn('shipping_firstname');
            $table->removeColumn('invoice_firstname');
            $table->removeColumn('shipping_lastname');
            $table->removeColumn('invoice_lastname');
        });
    }
};
