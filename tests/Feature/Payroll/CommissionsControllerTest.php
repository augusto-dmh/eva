<?php

use App\Enums\PayrollCycleStatus;
use App\Models\Collaborator;
use App\Models\PayrollCycle;
use App\Models\User;

describe('CommissionsController', function () {
    it('admin can access commissions page for a cycle', function () {
        $admin = User::factory()->admin()->create();
        $cycle = PayrollCycle::factory()->create(['status' => PayrollCycleStatus::AguardandoComissoes]);

        $this->actingAs($admin)
            ->get("/payroll-cycles/{$cycle->id}/commissions")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('payroll-cycles/Commissions'));
    });

    it('admin can update commission on a payroll entry', function () {
        $admin = User::factory()->admin()->create();
        $cycle = PayrollCycle::factory()->create();
        // Create a closer collaborator with an entry
        $collaborator = Collaborator::factory()->closer()->create();
        $entry = $cycle->entries()->create([
            'collaborator_id' => $collaborator->id,
            'tipo_contrato' => $collaborator->tipo_contrato->value,
            'legal_entity_id' => $collaborator->legal_entity_id,
            'salario_bruto' => $collaborator->salario_base,
        ]);

        $this->actingAs($admin)
            ->put("/payroll-entries/{$entry->id}", ['valor_comissao_bruta' => '1000'])
            ->assertRedirect();

        $entry->refresh();
        expect($entry->valor_comissao_bruta)->not->toBeNull();
        expect((float) $entry->valor_dsr)->toBeGreaterThan(0);
    });
});
