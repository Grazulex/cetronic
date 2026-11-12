<?php

declare(strict_types=1);

namespace App\SiteTexts;

use App\Actions\Translations\ExportTranslationsAction;
use Throwable;

/**
 * Saves a site text, then publishes it by regenerating the lang files.
 * The value stays saved when the export fails: the next save or the next
 * deploy (translations:export) will publish it.
 */
final class SaveSiteTextAction
{
    public function __construct(
        private readonly SiteTextRepository $repository,
        private readonly ExportTranslationsAction $export,
    ) {}

    /**
     * @param  array<string, ?string>  $values  indexed by locale (fr, nl, en)
     */
    public function handle(SiteText $text, array $values): SaveResult
    {
        $saved = $this->repository->save($text, $values);

        try {
            $this->export->handle();
        } catch (Throwable $e) {
            report($e);

            return new SaveResult($saved, published: false, error: $e->getMessage());
        }

        return new SaveResult($saved, published: true);
    }
}
