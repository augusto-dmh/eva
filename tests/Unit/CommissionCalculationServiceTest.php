<?php

use App\Services\Payroll\CommissionCalculationService;

describe('CommissionCalculationService', function () {
    it('calculates closer DSR correctly', function () {
        $service = new CommissionCalculationService;
        $result = $service->calculateCloserCommission('1000', 22, 9);
        // DSR = 1000 / 22 * 9 ≈ 409.09
        expect((float) $result['valor_dsr'])->toBeGreaterThan(409.0)->toBeLessThan(410.0);
        // total = 1000 + 409.09 ≈ 1409.09
        expect((float) $result['valor_comissao_total'])->toBeGreaterThan(1409.0)->toBeLessThan(1410.0);
    });

    it('applies advisor minimum guarantee when commission is below minimum', function () {
        $service = new CommissionCalculationService;
        // commission=500, garantido=1000, proLabore=1500 → effective = max(500,1000)-1500 = 1000-1500 = -500 → clamp to 0
        $result = $service->calculateAdvisorCommission('500', '1000', '1500', 22, 31);
        expect((float) $result['valor_comissao_total'])->toBe(0.0);
    });

    it('uses actual commission when above minimum for advisor', function () {
        $service = new CommissionCalculationService;
        // commission=3000, garantido=1000, proLabore=2000 → effective = 3000-2000 = 1000
        $result = $service->calculateAdvisorCommission('3000', '1000', '2000', 22, 31);
        expect((float) $result['valor_comissao_total'])->toBeGreaterThan(999.9)->toBeLessThan(1000.1);
    });
});
