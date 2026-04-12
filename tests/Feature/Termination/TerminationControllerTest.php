<?php

use App\Enums\CollaboratorStatus;
use App\Enums\TerminationStatus;
use App\Models\Collaborator;
use App\Models\TerminationRecord;
use App\Models\User;

describe('TerminationController', function () {
    it('admin can access termination create page', function () {
        $admin = User::factory()->admin()->create();
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => now()->subYears(2)->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get("/collaborators/{$collaborator->id}/termination/create")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('terminations/Create'));
    });

    it('admin can create a termination record', function () {
        $admin = User::factory()->admin()->create();
        $collaborator = Collaborator::factory()->clt()->create([
            'salario_base' => 3000,
            'data_admissao' => now()->subYears(2)->toDateString(),
        ]);

        $this->actingAs($admin)
            ->post("/collaborators/{$collaborator->id}/termination", [
                'tipo_desligamento' => 'pedido_demissao',
                'data_comunicacao' => now()->toDateString(),
                'data_efetivacao' => now()->addDays(30)->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('termination_records', [
            'collaborator_id' => $collaborator->id,
            'tipo_desligamento' => 'pedido_demissao',
            'status' => 'iniciado',
        ]);
    });

    it('admin can view termination record', function () {
        $admin = User::factory()->admin()->create();
        $record = TerminationRecord::factory()->create([
            'processado_por_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get("/termination-records/{$record->id}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('terminations/Show'));
    });

    it('admin can advance termination status', function () {
        $admin = User::factory()->admin()->create();
        $record = TerminationRecord::factory()->iniciado()->create([
            'processado_por_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put("/termination-records/{$record->id}", ['status' => 'simulacao_realizada'])
            ->assertRedirect();

        $record->refresh();
        expect($record->status)->toBe(TerminationStatus::SimulacaoRealizada);
    });

    it('collaborator status becomes desligado on Concluido', function () {
        $admin = User::factory()->admin()->create();
        $collaborator = Collaborator::factory()->clt()->create([
            'status' => CollaboratorStatus::Ativo,
            'data_admissao' => now()->subYears(1)->toDateString(),
        ]);
        $record = TerminationRecord::factory()->create([
            'collaborator_id' => $collaborator->id,
            'status' => TerminationStatus::DocumentacaoEnviada,
            'processado_por_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put("/termination-records/{$record->id}", ['status' => 'concluido'])
            ->assertRedirect();

        $collaborator->refresh();
        expect($collaborator->status)->toBe(CollaboratorStatus::Desligado);
    });

    it('admin can mark flash as cancelled', function () {
        $admin = User::factory()->admin()->create();
        $record = TerminationRecord::factory()->create([
            'flash_cancelado' => false,
            'processado_por_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put("/termination-records/{$record->id}", ['flash_cancelado' => true])
            ->assertRedirect();

        $record->refresh();
        expect($record->flash_cancelado)->toBeTrue();
    });
});
