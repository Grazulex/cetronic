<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('pubs', function (Blueprint $table): void {
            $table->id();
            $table->enum('type', ['pub_1', 'pub_2', 'pub_3', 'pub_4', 'pub_5'])->default('pub_1');
            $table->enum('language', ['en','nl','fr'])->default('fr');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('picture')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pubs');
    }
};
