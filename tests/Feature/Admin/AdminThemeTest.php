<?php

declare(strict_types=1);

use Filament\Facades\Filament;

it('loads the admin theme compiled by vite', function (): void {
    expect(invade(Filament::getPanel('admin'))->viteTheme)
        ->toBe('resources/css/filament/admin/theme.css');
});
