<?php

use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\User;
use App\Services\AdmissionChecklistService;

describe('AdmissionChecklistController', function () {
    it('admin can view a checklist', function () {
        $admin = User::factory()->admin()->create();
        $service = new AdmissionChecklistService;
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => now()->toDateString(),
        ]);
        $checklist = $service->createForCollaborator($collaborator);

        $this->actingAs($admin)
            ->get("/admission-checklists/{$checklist->id}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('admission-checklists/Show'));
    });

    it('creating a collaborator auto-creates a checklist', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();

        $this->actingAs($admin)
            ->post('/collaborators', [
                'nome_completo' => 'Test User',
                'email_corporativo' => 'test@example.com',
                'cpf' => '123.456.789-09',
                'tipo_contrato' => 'clt',
                'legal_entity_id' => $entity->id,
                'status' => 'ativo',
                'data_admissao' => now()->toDateString(),
                'salario_base' => 3000,
                'tipo_comissao' => 'none',
            ]);

        $collaborator = Collaborator::where('email_corporativo', 'test@example.com')->first();
        expect($collaborator->admissionChecklist)->not->toBeNull();
        expect($collaborator->admissionChecklist->items()->count())->toBeGreaterThan(0);
    });

    it('admin can confirm a checklist item', function () {
        $admin = User::factory()->admin()->create();
        $service = new AdmissionChecklistService;
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => now()->toDateString(),
        ]);
        $checklist = $service->createForCollaborator($collaborator);
        $item = $checklist->items()->first();

        $this->actingAs($admin)
            ->put("/admission-checklist-items/{$item->id}")
            ->assertRedirect();

        $item->refresh();
        expect($item->confirmado)->toBeTrue();
    });
});
