<?php

namespace App\Services\Payroll;

use Carbon\Carbon;

class B3BusinessDayService
{
    /**
     * Brazilian national holidays + B3 closures for 2024, 2025, 2026.
     *
     * @var array<string, array<string>>
     */
    private array $holidays = [
        2024 => [
            '2024-01-01',
            '2024-02-12',
            '2024-02-13',
            '2024-03-29',
            '2024-04-21',
            '2024-05-01',
            '2024-05-30',
            '2024-09-07',
            '2024-10-12',
            '2024-11-02',
            '2024-11-15',
            '2024-11-20',
            '2024-12-24',
            '2024-12-25',
            '2024-12-31',
        ],
        2025 => [
            '2025-01-01',
            '2025-03-03',
            '2025-03-04',
            '2025-04-18',
            '2025-04-21',
            '2025-05-01',
            '2025-06-19',
            '2025-09-07',
            '2025-10-12',
            '2025-11-02',
            '2025-11-15',
            '2025-11-20',
            '2025-12-24',
            '2025-12-25',
            '2025-12-31',
        ],
        2026 => [
            '2026-01-01',
            '2026-02-16',
            '2026-02-17',
            '2026-04-03',
            '2026-04-21',
            '2026-05-01',
            '2026-06-04',
            '2026-09-07',
            '2026-10-12',
            '2026-11-02',
            '2026-11-15',
            '2026-11-20',
            '2026-12-24',
            '2026-12-25',
            '2026-12-31',
        ],
    ];

    public function isBusinessDay(Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return false;
        }

        $yearHolidays = $this->holidays[$date->year] ?? [];

        return ! in_array($date->format('Y-m-d'), $yearHolidays, true);
    }

    public function countBusinessDays(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();

        while ($current->lte($endDay)) {
            if ($this->isBusinessDay($current)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    public function getBusinessDaysInMonth(int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        return $this->countBusinessDays($start, $end);
    }

    public function getRestDaysInMonth(int $year, int $month): int
    {
        $totalDays = Carbon::create($year, $month)->daysInMonth;
        $businessDays = $this->getBusinessDaysInMonth($year, $month);

        return $totalDays - $businessDays;
    }
}
