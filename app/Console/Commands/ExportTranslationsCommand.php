<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Translations\ExportTranslationsAction;
use Illuminate\Console\Command;

/**
 * Called by the Forge deploy script after "composer install": regenerates
 * lang/ from the database, where the client's site texts live.
 */
final class ExportTranslationsCommand extends Command
{
    protected $signature = 'translations:export';

    protected $description = 'Export the site texts from the database (ltu_* tables) to the lang directory';

    public function handle(ExportTranslationsAction $export): int
    {
        $files = $export->handle();

        $this->info(sprintf('Translations exported: %d files.', count($files)));

        return self::SUCCESS;
    }
}
