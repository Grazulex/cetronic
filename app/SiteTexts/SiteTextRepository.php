<?php

declare(strict_types=1);

namespace App\SiteTexts;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
     * Writes the given languages of a text. Unchanged languages are not touched,
     * so their updated_at stays meaningful; a language without row gets one,
     * linked to the English source phrase like the package did.
     *
     * @param  array<string, ?string>  $values  indexed by locale (fr, nl, en)
     */
    public function save(SiteText $text, array $values): SiteText
    {
        DB::transaction(function () use ($text, $values): void {
            foreach (array_intersect_key($values, array_flip(SiteText::LOCALES)) as $locale => $value) {
                if ($value === $text->values[$locale]) {
                    continue;
                }

                $this->write($text, (string) $locale, $value);
            }
        });

        return $this->reload($text);
    }

    private function write(SiteText $text, string $locale, ?string $value): void
    {
        $translationId = $this->translationId($locale);

        $fileId = DB::table('ltu_translation_files')
            ->where('name', $text->file)
            ->where('extension', 'php')
            ->value('id');

        $existing = DB::table('ltu_phrases')
            ->where('translation_id', $translationId)
            ->where('translation_file_id', $fileId)
            ->where('key', $text->key)
            ->value('id');

        if (null !== $existing) {
            DB::table('ltu_phrases')->where('id', $existing)->update(['value' => $value, 'updated_at' => now()]);

            return;
        }

        DB::table('ltu_phrases')->insert([
            'uuid' => (string) Str::uuid(),
            'translation_id' => $translationId,
            'translation_file_id' => $fileId,
            'phrase_id' => $this->sourcePhraseId((int) $fileId, $text->key),
            'key' => $text->key,
            'group' => $text->file,
            'value' => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * The package seeds every language, but a site language may still lack its
     * translation row (or, on a fresh database, its language row): create them.
     */
    private function translationId(string $locale): int
    {
        $languageId = DB::table('ltu_languages')->where('code', $locale)->value('id')
            ?? DB::table('ltu_languages')->insertGetId(['name' => mb_strtoupper($locale), 'code' => $locale]);

        $translationId = DB::table('ltu_translations')->where('language_id', $languageId)->value('id')
            ?? DB::table('ltu_translations')->insertGetId([
                'language_id' => $languageId,
                'source' => 'en' === $locale,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return (int) $translationId;
    }

    private function sourcePhraseId(int $fileId, string $key): ?int
    {
        $id = DB::table('ltu_phrases')
            ->join('ltu_translations', 'ltu_translations.id', '=', 'ltu_phrases.translation_id')
            ->join('ltu_languages', 'ltu_languages.id', '=', 'ltu_translations.language_id')
            ->where('ltu_languages.code', 'en')
            ->where('ltu_phrases.translation_file_id', $fileId)
            ->where('ltu_phrases.key', $key)
            ->value('ltu_phrases.id');

        return null === $id ? null : (int) $id;
    }

    private function reload(SiteText $text): SiteText
    {
        $rows = $this->rows()->filter(fn(object $row): bool => $row->file === $text->file && $row->key === $text->key);

        return $this->toText($rows->values());
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
