<?php

use App\Enums\TerminationType;
use App\Models\Collaborator;
use App\Services\Payroll\TerminationCalculationService;
use Carbon\Carbon;

describe('TerminationCalculationService', function () {
    it('FGTS fine is 40% for DispensaSemJustaCausa', function () {
        $service = new TerminationCalculationService;
        $result = $service->calculateFgtsFine('10000', TerminationType::DispensaSemJustaCausa);
        expect((float) $result)->toBe(4000.0);
    });

    it('FGTS fine is 20% for MutuoAcordo', function () {
        $service = new TerminationCalculationService;
        $result = $service->calculateFgtsFine('10000', TerminationType::MutuoAcordo);
        expect((float) $result)->toBe(2000.0);
    });

    it('FGTS fine is 0 for PedidoDemissao', function () {
        $service = new TerminationCalculationService;
        $result = $service->calculateFgtsFine('10000', TerminationType::PedidoDemissao);
        expect((float) $result)->toBe(0.0);
    });

    it('FGTS fine is 0 for DispensaComJustaCausa', function () {
        $service = new TerminationCalculationService;
        $result = $service->calculateFgtsFine('10000', TerminationType::DispensaComJustaCausa);
        expect((float) $result)->toBe(0.0);
    });

    it('FGTS fine is 0 for TerminoContrato', function () {
        $service = new TerminationCalculationService;
        $result = $service->calculateFgtsFine('10000', TerminationType::TerminoContrato);
        expect((float) $result)->toBe(0.0);
    });

    it('notice period is 30 days salary for DispensaSemJustaCausa with 0 years', function () {
        $service = new TerminationCalculationService;
        $collaborator = Collaborator::factory()->clt()->create([
            'salario_base' => 3000,
            'data_admissao' => now()->subMonths(6)->toDateString(),
        ]);
        $result = $service->calculateNoticeIndemnity($collaborator, TerminationType::DispensaSemJustaCausa);
        expect((float) $result)->toBe(3000.0); // 3000/30 * 30 = 3000
    });

    it('notice period is capped at 90 days for 20+ years of service', function () {
        $service = new TerminationCalculationService;
        $collaborator = Collaborator::factory()->clt()->create([
            'salario_base' => 3000,
            'data_admissao' => now()->subYears(25)->toDateString(),
        ]);
        $result = $service->calculateNoticeIndemnity($collaborator, TerminationType::DispensaSemJustaCausa);
        expect((float) $result)->toBe(9000.0); // 3000/30 * 90 (capped)
    });

    it('notice period is 0 for PedidoDemissao', function () {
        $service = new TerminationCalculationService;
        $collaborator = Collaborator::factory()->clt()->create([
            'salario_base' => 3000,
            'data_admissao' => now()->subYears(5)->toDateString(),
        ]);
        $result = $service->calculateNoticeIndemnity($collaborator, TerminationType::PedidoDemissao);
        expect((float) $result)->toBe(0.0);
    });

    it('proportional salary for 15 days worked = salario / 2', function () {
        $service = new TerminationCalculationService;
        $collaborator = Collaborator::factory()->clt()->create([
            'salario_base' => 3000,
            'data_admissao' => now()->subYears(2)->toDateString(),
        ]);
        // Effective date on the 15th
        $effectiveDate = Carbon::create(now()->year, now()->month, 15);
        $result = $service->simulate($collaborator, TerminationType::PedidoDemissao, $effectiveDate);
        expect($result['salario_proporcional_dias'])->toBe(15);
        expect((float) $result['salario_proporcional_valor'])->toBe(1500.0);
    });
});
