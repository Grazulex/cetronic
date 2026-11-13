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

    /** Above this many characters, a plain text is edited on several lines. */
    public const MULTILINE_FROM = 150;

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

    /**
     * A plain text too long for a one-line field is edited in a multi-line
     * field; long (html) texts go to the rich editor instead.
     */
    public function multiline(): bool
    {
        if ($this->long) {
            return false;
        }

        foreach ($this->values as $value) {
            if (mb_strlen((string) $value) > self::MULTILINE_FROM) {
                return true;
            }
        }

        return false;
    }

    public function fullKey(): string
    {
        return "{$this->file}.{$this->key}";
    }
}
