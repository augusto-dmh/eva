<?php

use App\Ai\Agents\PayrollDiscrepancyAnalystAgent;
use App\Models\PayrollCycle;
use App\Models\User;
use Laravel\Ai\Ai;

describe('PayrollDiscrepancyController', function () {
    it('returns a discrepancy report', function () {
        $admin = User::factory()->admin()->create();
        $cycle = PayrollCycle::factory()->create();

        Ai::fakeAgent(PayrollDiscrepancyAnalystAgent::class, [
            'Análise de discrepâncias: divergência de 2.5% detectada na entidade holding.',
        ]);

        $this->actingAs($admin)
            ->postJson("/payroll-cycles/{$cycle->id}/discrepancy-analysis", [
                'accounting_data' => ['holding' => ['total' => 50000]],
            ])
            ->assertOk()
            ->assertJsonStructure(['report']);
    });

    it('validates accounting_data is required', function () {
        $admin = User::factory()->admin()->create();
        $cycle = PayrollCycle::factory()->create();

        $this->actingAs($admin)
            ->postJson("/payroll-cycles/{$cycle->id}/discrepancy-analysis", [])
            ->assertUnprocessable();
    });
});
