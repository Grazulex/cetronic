<?php

declare(strict_types=1);

namespace App\SiteTexts;

final readonly class SaveResult
{
    public function __construct(
        public SiteText $text,
        public bool $published,
        public ?string $error = null,
    ) {}
}
