<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table): void {
            $table->id();

            $table->string('reference')->unique();
            $table->string('slug')->unique();
            $table->string('master_reference')->nullable();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->boolean('is_new')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('price')->default(0);
            $table->integer('price_b2b')->default(0);
            $table->integer('price_promo')->default(0);
            $table->integer('price_special_1')->default(0);
            $table->integer('price_special_2')->default(0);
            $table->integer('price_special_3')->default(0);
            $table->integer('multiple_quantity')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
