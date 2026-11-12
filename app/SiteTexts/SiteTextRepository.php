<?php

declare(strict_types=1);

namespace App\SiteTexts;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reads and writes the site texts in the ltu_* tables (source of truth).
 */
final class SiteTextRepository
{
    public function __construct(private readonly SiteTextConfig $config) {}

    /**
     * @return Collection<int, SiteText> sorted by zone order, then name
     */
    public function all(): Collection
    {
        $zones = $this->config->zones();

        return $this->rows()
            ->groupBy(fn(object $row): string => $row->file . "\0" . $row->key)
            ->map(fn(Collection $rows): SiteText => $this->toText($rows))
            ->sortBy([
                fn(SiteText $a, SiteText $b): int => ($zones[$a->zone]['order'] ?? PHP_INT_MAX) <=> ($zones[$b->zone]['order'] ?? PHP_INT_MAX),
                fn(SiteText $a, SiteText $b): int => strnatcasecmp($a->label, $b->label),
            ])
            ->values();
    }

    public function find(string $id): ?SiteText
    {
        return $this->all()->first(fn(SiteText $text): bool => $text->id() === $id);
    }

    /**
     * @return Collection<int, object{id: int, file: string, key: string, locale: string, value: ?string, updated_at: ?string}>
     */
    private function rows(): Collection
    {
        return DB::table('ltu_phrases')
            ->join('ltu_translations', 'ltu_translations.id', '=', 'ltu_phrases.translation_id')
            ->join('ltu_languages', 'ltu_languages.id', '=', 'ltu_translations.language_id')
            ->join('ltu_translation_files', 'ltu_translation_files.id', '=', 'ltu_phrases.translation_file_id')
            ->where('ltu_translation_files.extension', 'php')
            ->whereIn('ltu_languages.code', SiteText::LOCALES)
            ->orderBy('ltu_phrases.id')
            ->get([
                'ltu_phrases.id',
                'ltu_translation_files.name as file',
                'ltu_phrases.key',
                'ltu_languages.code as locale',
                'ltu_phrases.value',
                'ltu_phrases.updated_at',
            ]);
    }

    /**
     * @param  Collection<int, object>  $rows  the rows of one file + key
     */
    private function toText(Collection $rows): SiteText
    {
        $first = $rows->first();
        $file = (string) $first->file;
        $key = (string) $first->key;

        $values = array_fill_keys(SiteText::LOCALES, null);
        foreach ($rows as $row) {
            $values[$row->locale] = $row->value;
        }

        $updatedAt = $rows->pluck('updated_at')->filter()->max();

        return new SiteText(
            file: $file,
            key: $key,
            zone: $this->config->zoneFor($file, $key),
            label: $this->config->labelFor($file, $key),
            values: $values,
            long: $this->config->isLongForced($file, $key) || $this->hasHtml($values),
            updatedAt: null === $updatedAt ? null : CarbonImmutable::parse((string) $updatedAt),
        );
    }

    /**
     * @param  array<string, ?string>  $values
     */
    private function hasHtml(array $values): bool
    {
        foreach ($values as $value) {
            if (null !== $value && 1 === preg_match('/<[a-z][^>]*>/i', $value)) {
                return true;
            }
        }

        return false;
    }
}
