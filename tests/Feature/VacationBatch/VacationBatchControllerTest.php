<?php

use App\Enums\VacationBatchStatus;
use App\Models\User;
use App\Models\VacationBatch;

describe('VacationBatchController', function () {
    it('admin can GET /vacation-batches', function () {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get('/vacation-batches')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('vacation-batches/Index'));
    });

    it('admin can POST /vacation-batches to create a batch', function () {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->post('/vacation-batches', [
                'mes_referencia' => '2026-10',
                'tipo' => 'clt',
            ])
            ->assertRedirect();
        $this->assertDatabaseHas('vacation_batches', [
            'mes_referencia' => '2026-10',
            'tipo' => 'clt',
            'periodo_aquisitivo_minimo_meses' => 12,
            'dias_ferias' => 30,
        ]);
    });

    it('admin can GET /vacation-batches/{id}', function () {
        $admin = User::factory()->admin()->create();
        $batch = VacationBatch::factory()->create(['criado_por_id' => $admin->id]);
        $this->actingAs($admin)
            ->get("/vacation-batches/{$batch->id}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('vacation-batches/Show'));
    });

    it('admin can advance batch status', function () {
        $admin = User::factory()->admin()->create();
        $batch = VacationBatch::factory()->create([
            'status' => VacationBatchStatus::Rascunho,
            'criado_por_id' => $admin->id,
        ]);
        $this->actingAs($admin)
            ->put("/vacation-batches/{$batch->id}", ['status' => 'calculado'])
            ->assertRedirect();
        // Status is set to Calculado by transition (job runs async)
    });

    it('non-admin cannot access vacation batches', function () {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/vacation-batches')
            ->assertForbidden();
    });

    it('rejects invalid status transition', function () {
        $admin = User::factory()->admin()->create();
        $batch = VacationBatch::factory()->create([
            'status' => VacationBatchStatus::Rascunho,
            'criado_por_id' => $admin->id,
        ]);
        $this->actingAs($admin)
            ->put("/vacation-batches/{$batch->id}", ['status' => 'concluido'])
            ->assertSessionHasErrors('status');
    });
});
