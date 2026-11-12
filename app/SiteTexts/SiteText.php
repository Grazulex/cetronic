<?php

declare(strict_types=1);

namespace App\SiteTexts;

use Carbon\CarbonImmutable;

/**
 * One site text: a translation key and its value in the three site languages.
 */
final readonly class SiteText
{
    /** Display order of the languages on the page. */
    public const LOCALES = ['fr', 'nl', 'en'];

    /**
     * @param  array<string, ?string>  $values  'fr', 'nl' and 'en', null when missing
     */
    public function __construct(
        public string $file,
        public string $key,
        public string $zone,
        public string $label,
        public array $values,
        public bool $long,
        public ?CarbonImmutable $updatedAt,
    ) {}

    public static function idFor(string $file, string $key): string
    {
        return mb_substr(sha1($file . ':' . $key), 0, 12);
    }

    /**
     * Stable identifier, safe in Livewire paths (keys contain dots).
     */
    public function id(): string
    {
        return self::idFor($this->file, $this->key);
    }

    public function fullKey(): string
    {
        return "{$this->file}.{$this->key}";
    }
}
