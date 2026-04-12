<?php

use App\Enums\ChecklistStatus;
use App\Models\Collaborator;
use App\Models\User;
use App\Services\AdmissionChecklistService;

describe('AdmissionChecklistService', function () {
    it('creates checklist with CLT items for CLT collaborator', function () {
        $service = new AdmissionChecklistService;
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => now()->toDateString(),
        ]);

        $checklist = $service->createForCollaborator($collaborator);

        expect($checklist->status)->toBe(ChecklistStatus::Pendente);
        expect($checklist->items()->count())->toBeGreaterThanOrEqual(4);
        expect($checklist->items()->where('descricao', 'LIKE', '%CTPS%')->exists())->toBeTrue();
    });

    it('confirms first item advances status to EmAndamento', function () {
        $service = new AdmissionChecklistService;
        $admin = User::factory()->admin()->create();
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => now()->toDateString(),
        ]);
        $checklist = $service->createForCollaborator($collaborator);
        $firstItem = $checklist->items()->first();

        $service->confirmItem($firstItem, $admin);

        $checklist->refresh();
        expect($checklist->status)->toBe(ChecklistStatus::EmAndamento);
    });

    it('confirming all mandatory items advances status to Completo', function () {
        $service = new AdmissionChecklistService;
        $admin = User::factory()->admin()->create();
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => now()->toDateString(),
        ]);
        $checklist = $service->createForCollaborator($collaborator);

        foreach ($checklist->items()->where('obrigatorio', true)->get() as $item) {
            $service->confirmItem($item, $admin);
        }

        $checklist->refresh();
        expect($checklist->status)->toBe(ChecklistStatus::Completo);
        expect($checklist->completado_em)->not->toBeNull();
    });

    it('optional items do not block Completo transition', function () {
        $service = new AdmissionChecklistService;
        $admin = User::factory()->admin()->create();
        $collaborator = Collaborator::factory()->clt()->create([
            'data_admissao' => now()->toDateString(),
        ]);
        $checklist = $service->createForCollaborator($collaborator);

        // Confirm only mandatory items, leave optional unconfirmed
        foreach ($checklist->items()->where('obrigatorio', true)->get() as $item) {
            $service->confirmItem($item, $admin);
        }

        $checklist->refresh();
        expect($checklist->status)->toBe(ChecklistStatus::Completo);
    });
});
