<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Support\Colors\Color;

it('loads the admin theme compiled by vite', function (): void {
    expect(invade(Filament::getPanel('admin'))->viteTheme)
        ->toBe('resources/css/filament/admin/theme.css');
});

it('shows the Cetronic logo on the login page', function (): void {
    $this->get('/admin/login')->assertOk()->assertSee('logo.svg', false);
});

it('uses the Boutique colours and fonts', function (): void {
    $panel = Filament::getPanel('admin');

    expect($panel->getFontFamily())->toBe('Inter')
        ->and($panel->getColors()['primary'])->toBe(Color::hex('#111111'))
        ->and($panel->getColors()['gray'])->toBe(Color::Stone);
});
