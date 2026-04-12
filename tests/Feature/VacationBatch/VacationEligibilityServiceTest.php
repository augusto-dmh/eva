<?php

use App\Models\Collaborator;
use App\Services\Payroll\VacationEligibilityService;
use Carbon\Carbon;

describe('VacationEligibilityService', function () {
    it('CLT collaborator with exactly 12 months is eligible', function () {
        $service = new VacationEligibilityService;
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => Carbon::now()->subMonths(12)->toDateString(),
            'salario_base' => 3000,
        ]);
        $result = $service->computeEligibility($collaborator, Carbon::now());
        expect($result['elegivel'])->toBeTrue();
    });

    it('CLT collaborator with 11 months is not eligible', function () {
        $service = new VacationEligibilityService;
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => Carbon::now()->subMonths(11)->toDateString(),
        ]);
        $result = $service->computeEligibility($collaborator, Carbon::now());
        expect($result['elegivel'])->toBeFalse();
    });

    it('Estagiário with 6 months is eligible', function () {
        $service = new VacationEligibilityService;
        $collaborator = Collaborator::factory()->estagiario()->create([
            'data_admissao' => Carbon::now()->subMonths(6)->toDateString(),
            'salario_base' => 1500,
        ]);
        $result = $service->computeEligibility($collaborator, Carbon::now());
        expect($result['elegivel'])->toBeTrue();
    });

    it('Estagiário with 5 months is not eligible', function () {
        $service = new VacationEligibilityService;
        $collaborator = Collaborator::factory()->estagiario()->create([
            'data_admissao' => Carbon::now()->subMonths(5)->toDateString(),
        ]);
        $result = $service->computeEligibility($collaborator, Carbon::now());
        expect($result['elegivel'])->toBeFalse();
    });

    it('PJ collaborator is never eligible', function () {
        $service = new VacationEligibilityService;
        $collaborator = Collaborator::factory()->pj()->create([
            'data_admissao' => Carbon::now()->subYears(5)->toDateString(),
        ]);
        $result = $service->computeEligibility($collaborator, Carbon::now());
        expect($result['elegivel'])->toBeFalse();
    });

    it('computes vacation pay correctly for CLT', function () {
        $service = new VacationEligibilityService;
        $collaborator = Collaborator::factory()->clt()->create(['salario_base' => 3000]);
        $pay = $service->computeVacationPay($collaborator);
        expect($pay['valor_ferias'])->toBe(3000.0); // (3000/30)*30
        expect($pay['valor_terco_constitucional'])->toBe(1000.0); // 3000/3
    });

    it('computes vacation pay correctly for Estagiário (15 days)', function () {
        $service = new VacationEligibilityService;
        $collaborator = Collaborator::factory()->estagiario()->create(['salario_base' => 1500]);
        $pay = $service->computeVacationPay($collaborator);
        expect($pay['valor_ferias'])->toBe(750.0); // (1500/30)*15
        expect($pay['valor_terco_constitucional'])->toBe(250.0); // 750/3
    });
});
