<?php

declare(strict_types=1);

namespace App\Actions\Translations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds to the ltu_* tables the keys that exist in lang/{fr,nl,en}/*.php but
 * not in the database. Existing values are never overwritten: the database
 * (edited by the client) stays the source of truth.
 */
final class ImportMissingTranslationsAction
{
    public function __construct(private readonly Filesystem $filesystem) {}

    /**
     * @return int the number of keys added
     */
    public function handle(): int
    {
        $added = 0;

        // English first: French and Dutch phrases are linked to it.
        foreach (['en', 'fr', 'nl'] as $locale) {
            $directory = lang_path($locale);

            if ( ! $this->filesystem->isDirectory($directory)) {
                continue;
            }

            foreach ($this->filesystem->files($directory) as $file) {
                if ('php' !== $file->getExtension()) {
                    continue;
                }

                $added += $this->importFile($locale, $file->getFilenameWithoutExtension(), (array) $this->filesystem->getRequire($file->getPathname()));
            }
        }

        return $added;
    }

    /**
     * @param  array<mixed>  $translations
     */
    private function importFile(string $locale, string $name, array $translations): int
    {
        $values = array_filter(Arr::dot($translations), fn(mixed $value): bool => is_string($value) || is_int($value) || is_float($value));

        if ([] === $values) {
            return 0;
        }

        $translationId = $this->translationId($locale);
        $fileId = $this->fileId($name);

        $existing = DB::table('ltu_phrases')
            ->where('translation_id', $translationId)
            ->where('translation_file_id', $fileId)
            ->pluck('key')
            ->flip();

        $added = 0;

        foreach ($values as $key => $value) {
            $key = (string) $key;

            if ($existing->has($key)) {
                continue;
            }

            DB::table('ltu_phrases')->insert([
                'uuid' => (string) Str::uuid(),
                'translation_id' => $translationId,
                'translation_file_id' => $fileId,
                'phrase_id' => 'en' === $locale ? null : $this->sourcePhraseId($fileId, $key),
                'key' => $key,
                'group' => $name,
                'value' => (string) $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $added++;
        }

        return $added;
    }

    private function translationId(string $locale): int
    {
        $languageId = DB::table('ltu_languages')->where('code', $locale)->value('id')
            ?? DB::table('ltu_languages')->insertGetId(['name' => mb_strtoupper($locale), 'code' => $locale]);

        return (int) (DB::table('ltu_translations')->where('language_id', $languageId)->value('id')
            ?? DB::table('ltu_translations')->insertGetId([
                'language_id' => $languageId,
                'source' => 'en' === $locale,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
    }

    private function fileId(string $name): int
    {
        return (int) (DB::table('ltu_translation_files')->where('name', $name)->where('extension', 'php')->value('id')
            ?? DB::table('ltu_translation_files')->insertGetId(['name' => $name, 'extension' => 'php']));
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
}
