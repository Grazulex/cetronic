<?php

declare(strict_types=1);

namespace App\SiteTexts;

use Illuminate\Support\Str;

/**
 * Reads config/site-texts.php: zones, readable names and long texts.
 */
final class SiteTextConfig
{
    public const FALLBACK_ZONE = 'other';

    /**
     * @return array<string, array{label: string, order: int, technical: bool}>
     */
    public function zones(): array
    {
        $zones = [];

        foreach ((array) config('site-texts.zones', []) as $id => $zone) {
            $zones[(string) $id] = [
                'label' => (string) $zone['label'],
                'order' => (int) $zone['order'],
                'technical' => (bool) ($zone['technical'] ?? false),
            ];
        }

        uasort($zones, fn(array $a, array $b): int => $a['order'] <=> $b['order']);

        return $zones;
    }

    public function zoneFor(string $file, string $key): string
    {
        $best = self::FALLBACK_ZONE;
        $bestLength = -1;

        foreach ((array) config('site-texts.zones', []) as $id => $zone) {
            foreach ((array) ($zone['match'] ?? []) as $matchFile => $prefix) {
                $prefix = (string) $prefix;

                if ($matchFile === $file && str_starts_with($key, $prefix) && mb_strlen($prefix) > $bestLength) {
                    $best = (string) $id;
                    $bestLength = mb_strlen($prefix);
                }
            }
        }

        return $best;
    }

    public function labelFor(string $file, string $key): string
    {
        // Labels are keyed by 'file.key' (with dots): read the array, not config('a.b.c').
        $configured = ((array) config('site-texts.labels', []))["{$file}.{$key}"] ?? null;

        if (is_string($configured) && '' !== $configured) {
            return $configured;
        }

        return collect(explode('.', $key))
            ->map(fn(string $segment): string => Str::ucfirst(str_replace(['_', '-'], ' ', $segment)))
            ->implode(' › ');
    }

    public function isLongForced(string $file, string $key): bool
    {
        return in_array("{$file}.{$key}", (array) config('site-texts.long', []), true);
    }

    /**
     * @return list<string>
     */
    public function untranslatedExceptions(): array
    {
        return array_values(array_map('strval', (array) config('site-texts.untranslated_exceptions', [])));
    }
}
