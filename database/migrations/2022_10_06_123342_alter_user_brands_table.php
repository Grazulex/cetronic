<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->string('category_meta_value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_brands', function (Blueprint $table): void {
            $table->removeColumn('category_meta_value');
        });
    }
};
