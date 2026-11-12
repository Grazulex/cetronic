<?php

declare(strict_types=1);

namespace App\DataObjects\BrandDiscounts;

final class ImportReport
{
    /**
     * @param  array<int, array{row: int, column: string, message: string}>  $errors
     */
    public function __construct(
        public readonly array $errors = [],
        public readonly int $rulesCreated = 0,
        public readonly int $rulesUpdated = 0,
        public readonly int $usersUpdated = 0,
        public readonly int $unchanged = 0,
    ) {}

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
    }

    /**
     * Livewire-friendly representation.
     *
     * @return array{errors: array<int, array{row: int, column: string, message: string}>, summary: array<int, string>}
     */
    public function toArray(): array
    {
        return [
            'errors' => $this->errors,
            'summary' => [
                self::count($this->rulesCreated, 'remise créée', 'remises créées'),
                self::count($this->rulesUpdated, 'remise modifiée', 'remises modifiées'),
                self::count($this->usersUpdated, 'client mis à jour', 'clients mis à jour'),
                self::count($this->unchanged, 'client inchangé', 'clients inchangés'),
            ],
        ];
    }

    private static function count(int $count, string $singular, string $plural): string
    {
        return $count . ' ' . ($count > 1 ? $plural : $singular);
    }
}
