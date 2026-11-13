<?php

declare(strict_types=1);

namespace App\DataObjects\Stats;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * The month shown on the dashboard and the ranges it is compared with.
 *
 * The current month runs from the 1st to today and is compared "at the same
 * date": 1st–13 Nov vs 1st–13 Oct (clamped to the end of a shorter month).
 * A past month is compared as a whole with the whole previous month.
 */
final readonly class DashboardPeriod
{
    private function __construct(
        public Carbon $from,
        public Carbon $to,
        public Carbon $previousFrom,
        public Carbon $previousTo,
        public Carbon $lastYearFrom,
        public Carbon $lastYearTo,
        public bool $isCurrentMonth,
    ) {}

    /**
     * @param  ?string  $month  'Y-m'; null, invalid or future → current month
     */
    public static function for(?string $month, Carbon $now): self
    {
        $start = self::parseMonth($month);
        $currentStart = $now->copy()->startOfMonth()->startOfDay();

        if (null === $start || $start->gt($currentStart)) {
            $start = $currentStart;
        }

        $isCurrent = $start->equalTo($currentStart);
        $from = $start->copy();

        if ($isCurrent) {
            $to = $now->copy()->startOfDay();

            return new self(
                $from,
                $to,
                $from->copy()->subMonthNoOverflow(),
                self::sameDay($from->copy()->subMonthNoOverflow(), $to->day),
                $from->copy()->subYear(),
                self::sameDay($from->copy()->subYear(), $to->day),
                true,
            );
        }

        return new self(
            $from,
            $from->copy()->endOfMonth()->startOfDay(),
            $from->copy()->subMonthNoOverflow(),
            $from->copy()->subMonthNoOverflow()->endOfMonth()->startOfDay(),
            $from->copy()->subYear(),
            $from->copy()->subYear()->endOfMonth()->startOfDay(),
            false,
        );
    }

    public function label(): string
    {
        return Str::ucfirst($this->from->copy()->locale('fr')->translatedFormat('F Y'));
    }

    public function rangeLabel(): string
    {
        if ($this->isCurrentMonth) {
            return sprintf(
                'Du 1er au %d %s · comparé au 1er–%d %s',
                $this->to->day,
                $this->monthName($this->from),
                $this->previousTo->day,
                $this->monthName($this->previousFrom),
            );
        }

        return sprintf(
            '%s · comparé à %s %d',
            $this->label(),
            $this->monthName($this->previousFrom),
            $this->previousFrom->year,
        );
    }

    public function previousMonth(): string
    {
        return $this->previousFrom->format('Y-m');
    }

    public function nextMonth(): ?string
    {
        return $this->isCurrentMonth ? null : $this->from->copy()->addMonthNoOverflow()->format('Y-m');
    }

    public function filters(): SalesFilters
    {
        return new SalesFilters(from: $this->from, to: $this->to);
    }

    public function previousFilters(): SalesFilters
    {
        return new SalesFilters(from: $this->previousFrom, to: $this->previousTo);
    }

    public function lastYearFilters(): SalesFilters
    {
        return new SalesFilters(from: $this->lastYearFrom, to: $this->lastYearTo);
    }

    private static function parseMonth(?string $month): ?Carbon
    {
        if (null === $month || 1 !== preg_match('/^(\d{4})-(\d{2})$/', $month, $parts)) {
            return null;
        }

        if ((int) $parts[2] < 1 || (int) $parts[2] > 12) {
            return null;
        }

        return Carbon::create((int) $parts[1], (int) $parts[2], 1)->startOfDay();
    }

    /** Day $day of $monthStart's month, clamped to its last day. */
    private static function sameDay(Carbon $monthStart, int $day): Carbon
    {
        return $monthStart->copy()->day(min($day, $monthStart->daysInMonth))->startOfDay();
    }

    private function monthName(Carbon $date): string
    {
        return $date->copy()->locale('fr')->translatedFormat('F');
    }
}
