<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('item_publish_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // form_toggle, bulk_action, excel_import, image_import
            $table->boolean('old_value');
            $table->boolean('new_value');
            $table->string('reason')->nullable(); // La règle/raison du changement
            $table->json('metadata')->nullable(); // Données additionnelles (nom fichier, etc.)
            $table->timestamps();

            $table->index(['item_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_publish_logs');
    }
};
