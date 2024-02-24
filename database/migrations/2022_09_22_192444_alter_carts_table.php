<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->foreignId('shipping_location_id')->nullable()->constrained('locations')->onDelete('cascade');
            $table->foreignId('invoice_location_id')->nullable()->constrained('locations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->removeColumn('shipping_location_id');
            $table->removeColumn('invoice_location_id');
        });
    }
};
