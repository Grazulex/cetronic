<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * A translation slug was unique across every language, so a word spelled the
 * same in French and Dutch ("Accessoires") could not be saved twice.
 * It only has to be unique within one language.
 */
return new class () extends Migration {
    private const TABLES = ['category_translations', 'category_meta_translations'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropUnique("{$table}_slug_unique");
                $blueprint->unique(['locale', 'slug']);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropUnique("{$table}_locale_slug_unique");
                $blueprint->unique('slug');
            });
        }
    }
};
