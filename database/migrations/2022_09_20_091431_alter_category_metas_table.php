<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('category_metas', function (Blueprint $table): void {
            $table->boolean('is_meta')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('category_metas', function (Blueprint $table): void {
            $table->removeColumn('is_meta');
        });
    }
};
