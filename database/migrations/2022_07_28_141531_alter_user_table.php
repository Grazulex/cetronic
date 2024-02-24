<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->integer('franco')->default(0);
            $table->integer('shipping_price')->default(0);
            $table->string('external_reference')->nullable();
            $table->foreignId('agent_id')->nullable(true)->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('franco');
            $table->dropColumn('shipping_price');
            $table->dropColumn('agent_id');
            $table->dropColumn('external_reference');
        });
    }
};
