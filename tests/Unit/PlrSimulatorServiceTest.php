<?php

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\PlrRound;
use App\Models\User;
use App\Services\Payroll\PlrSimulatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('PlrSimulatorService', function () {
    it('distributes proportionally between two collaborators', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();

        $c1 = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 4000.00,
            'data_admissao' => '2025-01-01',
        ]);

        $c2 = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 4000.00,
            'data_admissao' => '2025-01-01',
        ]);

        $round = PlrRound::factory()->rascunho()->create([
            'ano_referencia' => 2025,
            'criado_por_id' => $admin->id,
        ]);

        (new PlrSimulatorService)->simulate($round, 10000.00);

        $e1 = $round->entries()->where('collaborator_id', $c1->id)->first();
        $e2 = $round->entries()->where('collaborator_id', $c2->id)->first();
        expect((float) $e1->valor_simulado)->toBe(5000.00)
            ->and((float) $e2->valor_simulado)->toBe(5000.00);
    });

    it('excludes collaborators with less than 6 months', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();

        $cShort = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 4000.00,
            'data_admissao' => '2025-09-01',
        ]);

        $round = PlrRound::factory()->rascunho()->create([
            'ano_referencia' => 2025,
            'criado_por_id' => $admin->id,
        ]);

        (new PlrSimulatorService)->simulate($round, 10000.00);

        expect($round->entries()->where('collaborator_id', $cShort->id)->exists())->toBeFalse();
    });

    it('calculates PLR IRRF correctly', function () {
        $service = new PlrSimulatorService;
        expect($service->calcularIrrfPlr(5000.00))->toBe(0.0)
            ->and($service->calcularIrrfPlr(7500.00))->toBe(round(7500 * 0.075 - 450, 2));
    });
});
