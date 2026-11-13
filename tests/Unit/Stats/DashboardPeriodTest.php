<?php

declare(strict_types=1);

use App\DataObjects\Stats\DashboardPeriod;
use Illuminate\Support\Carbon;

function dates(DashboardPeriod $period): array
{
    return [
        $period->from->toDateString(), $period->to->toDateString(),
        $period->previousFrom->toDateString(), $period->previousTo->toDateString(),
        $period->lastYearFrom->toDateString(), $period->lastYearTo->toDateString(),
    ];
}

it('compares the current month with the previous one at the same date', function (): void {
    $period = DashboardPeriod::for(null, Carbon::parse('2026-11-13 10:00'));

    expect(dates($period))->toBe(['2026-11-01', '2026-11-13', '2026-10-01', '2026-10-13', '2025-11-01', '2025-11-13'])
        ->and($period->isCurrentMonth)->toBeTrue()
        ->and($period->label())->toBe('Novembre 2026')
        ->and($period->rangeLabel())->toBe('Du 1er au 13 novembre · comparé au 1er–13 octobre')
        ->and($period->previousMonth())->toBe('2026-10')
        ->and($period->nextMonth())->toBeNull();
});

it('clamps the compared range to the end of a shorter month', function (): void {
    $period = DashboardPeriod::for(null, Carbon::parse('2026-03-31 18:00'));

    expect(dates($period))->toBe(['2026-03-01', '2026-03-31', '2026-02-01', '2026-02-28', '2025-03-01', '2025-03-31']);
});

it('compares a past month as a whole', function (): void {
    $period = DashboardPeriod::for('2026-09', Carbon::parse('2026-11-13'));

    expect(dates($period))->toBe(['2026-09-01', '2026-09-30', '2026-08-01', '2026-08-31', '2025-09-01', '2025-09-30'])
        ->and($period->isCurrentMonth)->toBeFalse()
        ->and($period->label())->toBe('Septembre 2026')
        ->and($period->rangeLabel())->toBe('Septembre 2026 · comparé à août 2026')
        ->and($period->nextMonth())->toBe('2026-10');
});

it('falls back to the current month for a future or invalid month', function (?string $month): void {
    $period = DashboardPeriod::for($month, Carbon::parse('2026-11-13'));

    expect($period->label())->toBe('Novembre 2026')->and($period->isCurrentMonth)->toBeTrue();
})->with(['future' => '2027-01', 'garbage' => 'nope', 'bad month' => '2026-13']);

it('builds the sales filters of the three ranges', function (): void {
    $period = DashboardPeriod::for(null, Carbon::parse('2026-11-13'));

    expect($period->filters()->from->toDateString())->toBe('2026-11-01')
        ->and($period->filters()->to->toDateString())->toBe('2026-11-13')
        ->and($period->previousFilters()->to->toDateString())->toBe('2026-10-13')
        ->and($period->lastYearFilters()->from->toDateString())->toBe('2025-11-01');
});
