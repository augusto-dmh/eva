<?php

use App\Services\Payroll\B3BusinessDayService;
use Carbon\Carbon;

describe('B3BusinessDayService', function () {
    it('considers New Year holiday as non-business day', function () {
        $service = new B3BusinessDayService;
        expect($service->isBusinessDay(Carbon::parse('2025-01-01')))->toBeFalse();
    });

    it('considers a regular Monday as business day', function () {
        $service = new B3BusinessDayService;
        expect($service->isBusinessDay(Carbon::parse('2025-01-06')))->toBeTrue(); // Monday
    });

    it('considers Saturday as non-business day', function () {
        $service = new B3BusinessDayService;
        expect($service->isBusinessDay(Carbon::parse('2025-01-04')))->toBeFalse(); // Saturday
    });

    it('counts business days in a known month', function () {
        $service = new B3BusinessDayService;
        $days = $service->getBusinessDaysInMonth(2025, 4); // April 2025: exclude holidays Tiradentes 04-21
        expect($days)->toBeInt()->toBeGreaterThan(18)->toBeLessThan(24);
    });

    it('rest days = total calendar days - business days', function () {
        $service = new B3BusinessDayService;
        $year = 2025;
        $month = 3;
        $totalDays = Carbon::create($year, $month)->daysInMonth;
        $businessDays = $service->getBusinessDaysInMonth($year, $month);
        $restDays = $service->getRestDaysInMonth($year, $month);
        expect($restDays)->toBe($totalDays - $businessDays);
    });
});
