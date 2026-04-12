<?php

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\DissidioRoundStatus;
use App\Enums\ProfessionalEventType;
use App\Models\Collaborator;
use App\Models\DissidioRound;
use App\Models\LegalEntity;
use App\Models\ProfessionalHistoryEntry;
use App\Models\User;
use App\Services\Payroll\DissidioSimulationService;

describe('DissidioSimulationService', function () {
    it('simulates 5% adjustment on CLT collaborator', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 5000.00,
        ]);

        $round = DissidioRound::factory()->create([
            'percentual' => 0.05,
            'aplica_estagiarios' => false,
            'status' => DissidioRoundStatus::Rascunho->value,
            'criado_por_id' => $admin->id,
        ]);

        (new DissidioSimulationService)->simulate($round);

        $entry = $round->entries()->where('collaborator_id', $collaborator->id)->first();
        expect($entry)->not->toBeNull()
            ->and((float) $entry->salario_novo)->toBe(5250.00)
            ->and($round->fresh()->status)->toBe(DissidioRoundStatus::Simulado);
    });

    it('excludes PJ collaborators from simulation', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $pj = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Pj,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 8000.00,
        ]);

        $round = DissidioRound::factory()->create([
            'percentual' => 0.05,
            'aplica_estagiarios' => false,
            'criado_por_id' => $admin->id,
        ]);

        (new DissidioSimulationService)->simulate($round);

        expect($round->entries()->where('collaborator_id', $pj->id)->exists())->toBeFalse();
    });

    it('excludes estagiario when aplica_estagiarios is false', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $estagiario = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Estagiario,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 2000.00,
        ]);

        $round = DissidioRound::factory()->create([
            'percentual' => 0.05,
            'aplica_estagiarios' => false,
            'criado_por_id' => $admin->id,
        ]);

        (new DissidioSimulationService)->simulate($round);

        expect($round->entries()->where('collaborator_id', $estagiario->id)->exists())->toBeFalse();
    });

    it('includes estagiario when aplica_estagiarios is true', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $estagiario = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Estagiario,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 2000.00,
        ]);

        $round = DissidioRound::factory()->create([
            'percentual' => 0.05,
            'aplica_estagiarios' => true,
            'criado_por_id' => $admin->id,
        ]);

        (new DissidioSimulationService)->simulate($round);

        expect($round->entries()->where('collaborator_id', $estagiario->id)->exists())->toBeTrue();
    });

    it('apply() updates collaborator salario_base', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 5000.00,
        ]);

        $round = DissidioRound::factory()->create([
            'percentual' => 0.05,
            'aplica_estagiarios' => false,
            'criado_por_id' => $admin->id,
        ]);

        $service = new DissidioSimulationService;
        $service->simulate($round);
        $service->apply($round, $admin);

        expect((float) $collaborator->fresh()->salario_base)->toBe(5250.00);
    });

    it('apply() creates ProfessionalHistoryEntry with tipo_evento Dissidio', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $collaborator = Collaborator::factory()->create([
            'legal_entity_id' => $entity->id,
            'tipo_contrato' => ContractType::Clt,
            'status' => CollaboratorStatus::Ativo,
            'salario_base' => 5000.00,
        ]);

        $round = DissidioRound::factory()->create([
            'percentual' => 0.05,
            'aplica_estagiarios' => false,
            'criado_por_id' => $admin->id,
        ]);

        $service = new DissidioSimulationService;
        $service->simulate($round);
        $service->apply($round, $admin);

        $history = ProfessionalHistoryEntry::where('collaborator_id', $collaborator->id)->first();
        expect($history)->not->toBeNull()
            ->and($history->tipo_evento)->toBe(ProfessionalEventType::Dissidio)
            ->and($history->campo_alterado)->toBe('salario_base')
            ->and((string) (float) $history->valor_anterior)->toBe('5000')
            ->and((string) (float) $history->valor_novo)->toBe('5250');
    });
});
