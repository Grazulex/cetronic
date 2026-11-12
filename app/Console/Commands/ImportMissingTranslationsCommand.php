<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Translations\ImportMissingTranslationsAction;
use Illuminate\Console\Command;

/**
 * Run by the deploy script BEFORE translations:export: new keys added by the
 * developer in lang/ are stored in the database, so the export keeps them.
 */
final class ImportMissingTranslationsCommand extends Command
{
    protected $signature = 'translations:import-missing';

    protected $description = 'Add to the database the translation keys of lang/{fr,nl,en} that are not there yet (never overwrites)';

    public function handle(ImportMissingTranslationsAction $import): int
    {
        $added = $import->handle();

        $this->info(sprintf('Translations imported: %d key%s added.', $added, 1 === $added ? '' : 's'));

        return self::SUCCESS;
    }
}
