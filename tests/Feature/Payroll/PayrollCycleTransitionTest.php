<?php

namespace Tests\Feature\Payroll;

use App\Enums\PayrollCycleStatus;
use App\Exceptions\InvalidTransitionException;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\PayrollCycle;
use App\Models\PayrollEntry;
use App\Models\User;
use App\Services\Payroll\PayrollCycleService;

describe('PayrollCycleService transitions', function () {
    it('transitions through all valid statuses in sequence', function () {
        $cycle = PayrollCycle::factory()->create();
        $service = app(PayrollCycleService::class);
        $user = User::factory()->admin()->create();

        $transitions = [
            PayrollCycleStatus::AguardandoNfPj,
            PayrollCycleStatus::AguardandoComissoes,
            PayrollCycleStatus::EmRevisao,
            PayrollCycleStatus::ConferidoContabilidade,
            PayrollCycleStatus::Fechado,
        ];

        foreach ($transitions as $to) {
            $service->transition($cycle, $to, $user);
            $cycle->refresh();
            expect($cycle->status)->toBe($to);
        }
    });

    it('throws InvalidTransitionException for backwards transition', function () {
        $cycle = PayrollCycle::factory()->create(['status' => PayrollCycleStatus::AguardandoNfPj]);
        $service = app(PayrollCycleService::class);

        expect(fn () => $service->transition($cycle, PayrollCycleStatus::Aberto))
            ->toThrow(InvalidTransitionException::class);
    });

    it('throws InvalidTransitionException from Fechado to any status', function () {
        $cycle = PayrollCycle::factory()->create(['status' => PayrollCycleStatus::Fechado]);
        $service = app(PayrollCycleService::class);

        expect(fn () => $service->transition($cycle, PayrollCycleStatus::ConferidoContabilidade))
            ->toThrow(InvalidTransitionException::class);
    });

    it('sets data_fechamento when closing a cycle', function () {
        $cycle = PayrollCycle::factory()->create(['status' => PayrollCycleStatus::ConferidoContabilidade]);
        $service = app(PayrollCycleService::class);

        expect($cycle->data_fechamento)->toBeNull();

        $service->transition($cycle, PayrollCycleStatus::Fechado);
        $cycle->refresh();

        expect($cycle->data_fechamento)->not->toBeNull();
    });

    it('aggregates totals from entries when closing', function () {
        $entity = LegalEntity::factory()->create();
        $cycle = PayrollCycle::factory()->create(['status' => PayrollCycleStatus::ConferidoContabilidade]);
        $collaborator1 = Collaborator::factory()->create(['legal_entity_id' => $entity->id]);
        $collaborator2 = Collaborator::factory()->create(['legal_entity_id' => $entity->id]);

        PayrollEntry::factory()->create([
            'payroll_cycle_id' => $cycle->id,
            'collaborator_id' => $collaborator1->id,
            'legal_entity_id' => $entity->id,
            'salario_bruto' => 5000,
            'valor_comissao_total' => 1000,
            'desconto_inss' => 500,
            'desconto_irrf' => 200,
            'desconto_contribuicao_assistencial' => 0,
            'desconto_petlove' => 0,
            'desconto_outros' => 0,
            'valor_liquido' => 5300,
            'valor_nota_fiscal_pj' => null,
        ]);

        PayrollEntry::factory()->create([
            'payroll_cycle_id' => $cycle->id,
            'collaborator_id' => $collaborator2->id,
            'legal_entity_id' => $entity->id,
            'salario_bruto' => 3000,
            'valor_comissao_total' => 500,
            'desconto_inss' => 300,
            'desconto_irrf' => 100,
            'desconto_contribuicao_assistencial' => 0,
            'desconto_petlove' => 0,
            'desconto_outros' => 0,
            'valor_liquido' => 3100,
            'valor_nota_fiscal_pj' => null,
        ]);

        $service = app(PayrollCycleService::class);
        $service->transition($cycle, PayrollCycleStatus::Fechado);
        $cycle->refresh();

        expect((float) $cycle->salarios_brutos)->toBe(8000.0);
        expect((float) $cycle->comissoes)->toBe(1500.0);
        expect((float) $cycle->deducoes)->toBe(1100.0);
        expect((float) $cycle->liquido)->toBe(8400.0);
    });

    it('creates a PayrollCycleEvent record on each transition', function () {
        $cycle = PayrollCycle::factory()->create();
        $service = app(PayrollCycleService::class);
        $user = User::factory()->admin()->create();

        $service->transition($cycle, PayrollCycleStatus::AguardandoNfPj, $user);

        expect($cycle->events()->count())->toBe(1);

        $event = $cycle->events()->first();
        expect($event->from_status)->toBe(PayrollCycleStatus::Aberto->value);
        expect($event->to_status)->toBe(PayrollCycleStatus::AguardandoNfPj->value);
        expect($event->triggered_by_id)->toBe($user->id);
    });
});
