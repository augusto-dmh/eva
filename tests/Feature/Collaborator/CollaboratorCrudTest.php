<?php

namespace Tests\Feature\Collaborator;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\User;

describe('CollaboratorController', function () {
    describe('index', function () {
        it('allows admin to list collaborators', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->get('/collaborators')
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('collaborators/Index'));
        });

        it('denies collaborator from listing', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->get('/collaborators')
                ->assertForbidden();
        });

        it('redirects guests to login', function () {
            $this->get('/collaborators')->assertRedirect('/login');
        });

        it('filters by search term', function () {
            $admin = User::factory()->admin()->create();
            LegalEntity::factory()->create();
            Collaborator::factory()->create(['nome_completo' => 'João da Silva']);
            Collaborator::factory()->create(['nome_completo' => 'Maria Oliveira']);

            $this->actingAs($admin)
                ->get('/collaborators?search=João')
                ->assertInertia(fn ($page) => $page
                    ->component('collaborators/Index')
                    ->where('collaborators.data.0.nome_completo', 'João da Silva')
                    ->where('collaborators.total', 1)
                );
        });

        it('filters by contract type', function () {
            $admin = User::factory()->admin()->create();
            LegalEntity::factory()->create();
            Collaborator::factory()->clt()->create();
            Collaborator::factory()->pj()->create();

            $this->actingAs($admin)
                ->get('/collaborators?tipo_contrato=clt')
                ->assertInertia(fn ($page) => $page
                    ->where('collaborators.total', 1)
                );
        });

        it('filters by status', function () {
            $admin = User::factory()->admin()->create();
            LegalEntity::factory()->create();
            Collaborator::factory()->create(['status' => CollaboratorStatus::Ativo]);
            Collaborator::factory()->terminated()->create();

            $this->actingAs($admin)
                ->get('/collaborators?status=ativo')
                ->assertInertia(fn ($page) => $page
                    ->where('collaborators.total', 1)
                );
        });
    });

    describe('create', function () {
        it('allows admin to access create form', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->get('/collaborators/create')
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('collaborators/Create'));
        });

        it('denies collaborator from accessing create form', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->get('/collaborators/create')
                ->assertForbidden();
        });
    });

    describe('store', function () {
        it('allows admin to create a collaborator', function () {
            $admin = User::factory()->admin()->create();
            $entity = LegalEntity::factory()->create();

            $this->actingAs($admin)
                ->post('/collaborators', [
                    'nome_completo' => 'Teste da Silva',
                    'cpf' => '123.456.789-00',
                    'email_corporativo' => 'teste@clubedovalor.com.br',
                    'tipo_contrato' => ContractType::Clt->value,
                    'legal_entity_id' => $entity->id,
                    'data_admissao' => '2025-01-15',
                    'salario_base' => '5000.00',
                ])
                ->assertRedirect('/collaborators');

            $this->assertDatabaseHas('collaborators', [
                'nome_completo' => 'Teste da Silva',
                'email_corporativo' => 'teste@clubedovalor.com.br',
            ]);
        });

        it('validates required fields', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->post('/collaborators', [])
                ->assertSessionHasErrors(['nome_completo', 'cpf', 'email_corporativo', 'tipo_contrato', 'legal_entity_id', 'data_admissao', 'salario_base']);
        });

        it('validates cpf uniqueness', function () {
            $admin = User::factory()->admin()->create();
            $entity = LegalEntity::factory()->create();
            Collaborator::factory()->create(['cpf' => '123.456.789-00']);

            $this->actingAs($admin)
                ->post('/collaborators', [
                    'nome_completo' => 'Outro Colaborador',
                    'cpf' => '123.456.789-00',
                    'email_corporativo' => 'outro@clubedovalor.com.br',
                    'tipo_contrato' => ContractType::Clt->value,
                    'legal_entity_id' => $entity->id,
                    'data_admissao' => '2025-01-15',
                    'salario_base' => '5000.00',
                ])
                ->assertSessionHasErrors('cpf');
        });

        it('validates email uniqueness', function () {
            $admin = User::factory()->admin()->create();
            $entity = LegalEntity::factory()->create();
            Collaborator::factory()->create(['email_corporativo' => 'used@clubedovalor.com.br']);

            $this->actingAs($admin)
                ->post('/collaborators', [
                    'nome_completo' => 'Novo Colaborador',
                    'cpf' => '987.654.321-00',
                    'email_corporativo' => 'used@clubedovalor.com.br',
                    'tipo_contrato' => ContractType::Clt->value,
                    'legal_entity_id' => $entity->id,
                    'data_admissao' => '2025-01-15',
                    'salario_base' => '5000.00',
                ])
                ->assertSessionHasErrors('email_corporativo');
        });

        it('denies collaborator from creating', function () {
            $user = User::factory()->create();
            $entity = LegalEntity::factory()->create();

            $this->actingAs($user)
                ->post('/collaborators', [
                    'nome_completo' => 'Test',
                    'cpf' => '111.111.111-11',
                    'email_corporativo' => 'test@test.com',
                    'tipo_contrato' => ContractType::Clt->value,
                    'legal_entity_id' => $entity->id,
                    'data_admissao' => '2025-01-15',
                    'salario_base' => '5000.00',
                ])
                ->assertForbidden();
        });
    });

    describe('show', function () {
        it('allows admin to view any collaborator', function () {
            $admin = User::factory()->admin()->create();
            $collaborator = Collaborator::factory()->create();

            $this->actingAs($admin)
                ->get("/collaborators/{$collaborator->id}")
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('collaborators/Show'));
        });

        it('denies collaborator from viewing via admin route', function () {
            $user = User::factory()->create();
            $collaborator = Collaborator::factory()->create();

            $this->actingAs($user)
                ->get("/collaborators/{$collaborator->id}")
                ->assertForbidden();
        });
    });

    describe('edit', function () {
        it('allows admin to access edit form', function () {
            $admin = User::factory()->admin()->create();
            $collaborator = Collaborator::factory()->create();

            $this->actingAs($admin)
                ->get("/collaborators/{$collaborator->id}/edit")
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('collaborators/Edit'));
        });

        it('denies collaborator from accessing edit form', function () {
            $user = User::factory()->create();
            $collaborator = Collaborator::factory()->create();

            $this->actingAs($user)
                ->get("/collaborators/{$collaborator->id}/edit")
                ->assertForbidden();
        });
    });

    describe('update', function () {
        it('allows admin to update a collaborator', function () {
            $admin = User::factory()->admin()->create();
            $collaborator = Collaborator::factory()->clt()->create();

            $this->actingAs($admin)
                ->put("/collaborators/{$collaborator->id}", array_merge($collaborator->toArray(), [
                    'nome_completo' => 'Nome Atualizado',
                    'legal_entity_id' => $collaborator->legal_entity_id,
                    'salario_base' => '8000.00',
                ]))
                ->assertRedirect("/collaborators/{$collaborator->id}");

            $this->assertDatabaseHas('collaborators', [
                'id' => $collaborator->id,
                'nome_completo' => 'Nome Atualizado',
            ]);
        });

        it('allows updating with same cpf', function () {
            $admin = User::factory()->admin()->create();
            $collaborator = Collaborator::factory()->clt()->create();

            $this->actingAs($admin)
                ->put("/collaborators/{$collaborator->id}", array_merge($collaborator->toArray(), [
                    'cpf' => $collaborator->cpf,
                    'legal_entity_id' => $collaborator->legal_entity_id,
                    'salario_base' => $collaborator->salario_base,
                ]))
                ->assertRedirect("/collaborators/{$collaborator->id}");
        });

        it('denies collaborator from updating', function () {
            $user = User::factory()->create();
            $collaborator = Collaborator::factory()->create();

            $this->actingAs($user)
                ->put("/collaborators/{$collaborator->id}", ['nome_completo' => 'Hack'])
                ->assertForbidden();
        });
    });
});
