<?php

use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\User;

describe('AssistiveConventionController', function () {
    it('admin can GET /union/opposition', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/union/opposition')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('union/Opposition'));
    });

    it('admin can POST to create a record', function () {
        $admin = User::factory()->admin()->create();
        $entity = LegalEntity::factory()->create();
        $collaborator = Collaborator::factory()->create(['legal_entity_id' => $entity->id]);

        $this->actingAs($admin)
            ->post('/union/opposition', [
                'collaborator_id' => $collaborator->id,
                'ano_referencia' => 2025,
                'fez_oposicao' => true,
                'data_oposicao' => '2025-03-01',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('assistive_convention_records', [
            'collaborator_id' => $collaborator->id,
            'fez_oposicao' => true,
        ]);
    });

    it('redirects guest', function () {
        $this->get('/union/opposition')->assertRedirect('/login');
    });
});
