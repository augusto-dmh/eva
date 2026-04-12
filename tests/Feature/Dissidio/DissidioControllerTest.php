<?php

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\DissidioRoundStatus;
use App\Models\Collaborator;
use App\Models\DissidioRound;
use App\Models\LegalEntity;
use App\Models\User;
use App\Services\Payroll\DissidioSimulationService;

describe('DissidioController', function () {
    describe('index', function () {
        it('admin can GET /dissidio-rounds', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->get('/dissidio-rounds')
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('dissidio/Index'));
        });

        it('redirects guest', function () {
            $this->get('/dissidio-rounds')->assertRedirect('/login');
        });
    });

    describe('store', function () {
        it('admin can POST to create dissidio round', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->post('/dissidio-rounds', [
                    'ano_referencia' => 2026,
                    'data_base' => '2026-02-01',
                    'percentual' => 0.055,
                    'aplica_estagiarios' => false,
                ])
                ->assertRedirect();

            $this->assertDatabaseHas('dissidio_rounds', [
                'ano_referencia' => 2026,
                'status' => 'rascunho',
            ]);
        });
    });

    describe('show', function () {
        it('admin can GET /dissidio-rounds/{id}', function () {
            $admin = User::factory()->admin()->create();
            $round = DissidioRound::factory()->create(['criado_por_id' => $admin->id]);

            $this->actingAs($admin)
                ->get("/dissidio-rounds/{$round->id}")
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('dissidio/Show'));
        });
    });

    describe('simulate', function () {
        it('admin can simulate a dissidio round', function () {
            $admin = User::factory()->admin()->create();
            $entity = LegalEntity::factory()->create();
            Collaborator::factory()->create([
                'legal_entity_id' => $entity->id,
                'tipo_contrato' => ContractType::Clt,
                'status' => CollaboratorStatus::Ativo,
                'salario_base' => 5000,
            ]);

            $round = DissidioRound::factory()->rascunho()->create([
                'percentual' => 0.05,
                'criado_por_id' => $admin->id,
            ]);

            $this->actingAs($admin)
                ->post("/dissidio-rounds/{$round->id}/simulate")
                ->assertRedirect();

            expect($round->fresh()->status)->toBe(DissidioRoundStatus::Simulado);
        });
    });

    describe('apply', function () {
        it('admin can apply and collaborator salary is updated', function () {
            $admin = User::factory()->admin()->create();
            $entity = LegalEntity::factory()->create();
            $collaborator = Collaborator::factory()->create([
                'legal_entity_id' => $entity->id,
                'tipo_contrato' => ContractType::Clt,
                'status' => CollaboratorStatus::Ativo,
                'salario_base' => 5000,
            ]);

            $round = DissidioRound::factory()->rascunho()->create([
                'percentual' => 0.05,
                'criado_por_id' => $admin->id,
            ]);

            $service = app(DissidioSimulationService::class);
            $service->simulate($round);

            $this->actingAs($admin)
                ->post("/dissidio-rounds/{$round->id}/apply")
                ->assertRedirect();

            expect((float) $collaborator->fresh()->salario_base)->toBe(5250.0)
                ->and($round->fresh()->status)->toBe(DissidioRoundStatus::Aplicado);
        });
    });
});
