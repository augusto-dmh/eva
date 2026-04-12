<?php

namespace Tests\Feature\PjInvoice;

use App\Enums\PayrollCycleStatus;
use App\Enums\PjInvoiceStatus;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\PayrollCycle;
use App\Models\PjInvoice;
use App\Models\User;

describe('PjInvoice admin', function () {
    it('admin can GET /payroll-cycles/{id}/pj-invoices', function () {
        $admin = User::factory()->admin()->create();
        $cycle = PayrollCycle::factory()->create();

        $this->actingAs($admin)
            ->get("/payroll-cycles/{$cycle->id}/pj-invoices")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('pj-invoices/Index'));
    });

    it('admin can PUT /pj-invoices/{id} to change status', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $uploader = User::factory()->create();
        $collaborator = Collaborator::factory()->pj()->create([
            'user_id' => $uploader->id,
            'legal_entity_id' => $entity->id,
        ]);
        $cycle = PayrollCycle::factory()->create([
            'status' => PayrollCycleStatus::AguardandoNfPj,
        ]);

        $invoice = PjInvoice::factory()->create([
            'collaborator_id' => $collaborator->id,
            'payroll_cycle_id' => $cycle->id,
            'uploaded_by_id' => $uploader->id,
            'status' => PjInvoiceStatus::Pendente,
        ]);

        $this->actingAs($admin)
            ->put("/pj-invoices/{$invoice->id}", [
                'status' => 'aprovada',
                'observacoes' => 'Nota aprovada.',
            ])
            ->assertRedirect();

        $invoice->refresh();
        expect($invoice->status)->toBe(PjInvoiceStatus::Aprovada);
        expect($invoice->observacoes)->toBe('Nota aprovada.');
        expect($invoice->revisado_por_id)->toBe($admin->id);
    });

    it('non-admin cannot PUT /pj-invoices/{id}', function () {
        $user = User::factory()->create();
        $entity = LegalEntity::factory()->create();
        $collaborator = Collaborator::factory()->pj()->create([
            'user_id' => $user->id,
            'legal_entity_id' => $entity->id,
        ]);
        $cycle = PayrollCycle::factory()->create([
            'status' => PayrollCycleStatus::AguardandoNfPj,
        ]);

        $invoice = PjInvoice::factory()->create([
            'collaborator_id' => $collaborator->id,
            'payroll_cycle_id' => $cycle->id,
            'uploaded_by_id' => $user->id,
            'status' => PjInvoiceStatus::Pendente,
        ]);

        $this->actingAs($user)
            ->put("/pj-invoices/{$invoice->id}", [
                'status' => 'aprovada',
                'observacoes' => '',
            ])
            ->assertForbidden();
    });
});
