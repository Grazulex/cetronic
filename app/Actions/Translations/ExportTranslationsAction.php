<?php

declare(strict_types=1);

namespace App\Actions\Translations;

use Brick\VarExporter\VarExporter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Writes the site texts stored in the ltu_* tables to lang/{locale}/{file}.
 *
 * The database is the source of truth: this replaces the export of the
 * outhebox/laravel-translations package (v0.0.6) with the same behaviour:
 * - phrases with a NULL value are skipped (empty strings are kept),
 * - dotted keys are nested with Arr::set, in id order,
 * - each file that has phrases is overwritten, other files are left untouched.
 */
final class ExportTranslationsAction
{
    public function __construct(private readonly Filesystem $filesystem) {}

    /**
     * @return list<string> the files written, relative to the lang directory
     */
    public function handle(): array
    {
        $written = [];

        $translations = DB::table('ltu_translations')
            ->join('ltu_languages', 'ltu_languages.id', '=', 'ltu_translations.language_id')
            ->orderBy('ltu_translations.id')
            ->get(['ltu_translations.id', 'ltu_languages.code']);

        foreach ($translations as $translation) {
            foreach ($this->filesFor((int) $translation->id) as $file => $phrases) {
                $relative = "{$translation->code}/{$file}";
                $this->write(lang_path($relative), $phrases);
                $written[] = $relative;
            }
        }

        return $written;
    }

    /**
     * @return array<string, array<string, mixed>> phrases tree per file name
     */
    private function filesFor(int $translationId): array
    {
        $phrases = DB::table('ltu_phrases')
            ->join('ltu_translation_files', 'ltu_translation_files.id', '=', 'ltu_phrases.translation_file_id')
            ->where('ltu_phrases.translation_id', $translationId)
            ->whereNotNull('ltu_phrases.value')
            ->orderBy('ltu_phrases.id')
            ->get(['ltu_phrases.key', 'ltu_phrases.value', 'ltu_translation_files.name', 'ltu_translation_files.extension']);

        $tree = [];

        foreach ($phrases as $phrase) {
            $file = "{$phrase->name}.{$phrase->extension}";
            $tree[$file] ??= [];
            Arr::set($tree[$file], (string) $phrase->key, $phrase->value);
        }

        return $tree;
    }

    /**
     * @param  array<string, mixed>  $phrases
     */
    private function write(string $path, array $phrases): void
    {
        $this->filesystem->ensureDirectoryExists(dirname($path), 0755);

        if ('json' === $this->filesystem->extension($path)) {
            $this->filesystem->put($path, (string) json_encode($phrases, JSON_PRETTY_PRINT));

            return;
        }

        $this->filesystem->put(
            $path,
            "<?php\n\nreturn " . VarExporter::export($phrases, VarExporter::TRAILING_COMMA_IN_ARRAY) . ';' . PHP_EOL,
        );
    }
}
