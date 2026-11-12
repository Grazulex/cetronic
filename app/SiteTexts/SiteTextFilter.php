<?php

declare(strict_types=1);

namespace App\SiteTexts;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Search and filters of the "Textes du site" page, applied in memory
 * (about 500 keys, three languages each).
 */
final class SiteTextFilter
{
    public const SHOW_ALL = 'all';

    public const SHOW_UNTRANSLATED = 'untranslated';

    public const SHOW_RECENT = 'recent';

    public const RECENT_DAYS = 30;

    public function __construct(private readonly SiteTextConfig $config) {}

    /**
     * @param  Collection<int, SiteText>  $texts
     * @return Collection<int, SiteText>
     */
    public function apply(Collection $texts, string $search = '', ?string $zone = null, string $show = self::SHOW_ALL, bool $technical = false): Collection
    {
        $zones = $this->config->zones();
        $search = Str::of($search)->trim()->value();

        return $texts
            ->filter(fn(SiteText $text): bool => null === $zone || '' === $zone
                ? $technical || ! ($zones[$text->zone]['technical'] ?? false)
                : $text->zone === $zone)
            ->filter(fn(SiteText $text): bool => match ($show) {
                self::SHOW_UNTRANSLATED => $this->needsTranslation($text),
                self::SHOW_RECENT => null !== $text->updatedAt && $text->updatedAt->greaterThanOrEqualTo(CarbonImmutable::now()->subDays(self::RECENT_DAYS)),
                default => true,
            })
            ->filter(fn(SiteText $text): bool => '' === $search
                || Str::contains($text->fullKey(), $search, ignoreCase: true)
                || in_array(true, $this->matches($text, $search), true))
            ->values();
    }

    /**
     * A text needs translation when a language is empty, or when Dutch or
     * English is still the French text (numbers and exceptions aside).
     */
    public function needsTranslation(SiteText $text): bool
    {
        foreach (SiteText::LOCALES as $locale) {
            if (null === $text->values[$locale] || '' === Str::of($text->values[$locale])->trim()->value()) {
                return true;
            }
        }

        $french = (string) $text->values['fr'];

        $french = Str::of($french)->trim()->value();

        if (is_numeric($french) || $this->isContactData($french) || in_array($french, $this->config->untranslatedExceptions(), true)) {
            return false;
        }

        return Str::of((string) $text->values['nl'])->trim()->value() === $french
            || Str::of((string) $text->values['en'])->trim()->value() === $french;
    }

    /**
     * @return array{label: bool, fr: bool, nl: bool, en: bool}
     */
    public function matches(SiteText $text, string $search): array
    {
        $search = Str::of($search)->trim()->value();
        $hit = fn(?string $value): bool => '' !== $search && null !== $value
            && Str::contains($text->long ? strip_tags($value) : $value, $search, ignoreCase: true);

        return [
            'label' => $hit($text->label),
            'fr' => $hit($text->values['fr']),
            'nl' => $hit($text->values['nl']),
            'en' => $hit($text->values['en']),
        ];
    }

    /**
     * E-mail addresses, phone numbers and web addresses are the same in
     * every language.
     */
    private function isContactData(string $value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_EMAIL)
            || 1 === preg_match('/^\+?[\d\s().\/-]{6,}$/', $value)
            || 1 === preg_match('#^(https?://|www\.)\S+$#i', $value);
    }
}
