<?php

declare(strict_types=1);

use App\SiteTexts\SiteText;

function plainSiteText(array $values, bool $long = false): SiteText
{
    return new SiteText('user', 'auth.notice', 'user', 'Notice', $values + ['fr' => null, 'nl' => null, 'en' => null], $long, null);
}

it('edits a plain text longer than 150 characters on several lines', function (): void {
    expect(plainSiteText(['nl' => str_repeat('a', 151)])->multiline())->toBeTrue();
});

it('keeps a plain text of 150 characters or less on one line', function (): void {
    expect(plainSiteText(['fr' => str_repeat('é', 150), 'en' => 'Short'])->multiline())->toBeFalse()
        ->and(plainSiteText([])->multiline())->toBeFalse();
});

it('never edits a long text on several lines: it goes to the rich editor', function (): void {
    expect(plainSiteText(['fr' => str_repeat('a', 300)], long: true)->multiline())->toBeFalse();
});
