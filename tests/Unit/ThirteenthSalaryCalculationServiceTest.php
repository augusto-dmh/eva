<?php

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\ThirteenthSalaryRound;
use App\Models\User;
use App\Services\Payroll\ThirteenthSalaryCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('ThirteenthSalaryCalculationService', function () {
    it('calculates valor_integral for full year (12 months)', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 6000.00,
            'data_admissao' => '2025-01-01',
        ]);

        $round = ThirteenthSalaryRound::factory()->create([
            'ano_referencia' => 2025,
            'criado_por_id' => $admin->id,
            'primeira_parcela_data_limite' => '2025-11-30',
            'segunda_parcela_data_limite' => '2025-12-20',
        ]);

        (new ThirteenthSalaryCalculationService)->simulate($round);

        $entry = $round->entries()->where('collaborator_id', $collaborator->id)->first();
        expect($entry)->not->toBeNull()
            ->and((float) $entry->valor_integral)->toBe(6000.00)
            ->and($entry->meses_trabalhados)->toBe(12);
    });

    it('calculates valor_integral for 6 months', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 6000.00,
            'data_admissao' => '2025-07-01',
        ]);

        $round = ThirteenthSalaryRound::factory()->create([
            'ano_referencia' => 2025,
            'criado_por_id' => $admin->id,
            'primeira_parcela_data_limite' => '2025-11-30',
            'segunda_parcela_data_limite' => '2025-12-20',
        ]);

        (new ThirteenthSalaryCalculationService)->simulate($round);

        $entry = $round->entries()->where('collaborator_id', $collaborator->id)->first();
        expect($entry)->not->toBeNull()
            ->and((float) $entry->valor_integral)->toBe(3000.00)
            ->and($entry->meses_trabalhados)->toBe(6);
    });

    it('primeira_parcela is 50% of valor_integral with no deductions', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 4000.00,
            'data_admissao' => '2025-01-01',
        ]);

        $round = ThirteenthSalaryRound::factory()->create([
            'ano_referencia' => 2025,
            'criado_por_id' => $admin->id,
            'primeira_parcela_data_limite' => '2025-11-30',
            'segunda_parcela_data_limite' => '2025-12-20',
        ]);

        (new ThirteenthSalaryCalculationService)->simulate($round);

        $entry = $round->entries()->where('collaborator_id', $collaborator->id)->first();
        expect((float) $entry->primeira_parcela_valor)->toBe(2000.00)
            ->and((float) $entry->desconto_inss)->toBeGreaterThan(0)
            ->and((float) $entry->segunda_parcela_valor)->toBeLessThan(2000.00);
    });

    it('excludes PJ collaborators', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $pj = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Pj,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 8000.00,
            'data_admissao' => '2025-01-01',
        ]);

        $round = ThirteenthSalaryRound::factory()->create([
            'ano_referencia' => 2025,
            'criado_por_id' => $admin->id,
            'primeira_parcela_data_limite' => '2025-11-30',
            'segunda_parcela_data_limite' => '2025-12-20',
        ]);

        (new ThirteenthSalaryCalculationService)->simulate($round);

        expect($round->entries()->where('collaborator_id', $pj->id)->exists())->toBeFalse();
    });
});
