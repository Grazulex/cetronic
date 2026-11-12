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
}
