<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('item_metas', function (Blueprint $table): void {
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
        });
    }

    public function down(): void {}
};
