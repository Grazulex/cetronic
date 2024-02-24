<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class () extends Migration {
    public function up(): void
    {
        $folders = ['items', 'brands', 'categories', 'items'];

        foreach ($folders as $folder) {
            $dir = storage_path($folder);
            if ( ! Storage::directoryExists($dir)) {
                Storage::makeDirectory($dir);
            }
        }
    }

    public function down(): void
    {
    }
};
