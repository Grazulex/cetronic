<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->dropColumn('category_id');
        });
    }
};
