<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('category_meta_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_meta_id')->constrained('category_metas')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_meta_translations');
    }
};
