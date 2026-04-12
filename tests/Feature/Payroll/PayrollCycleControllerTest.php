<?php

namespace Tests\Feature\Payroll;

use App\Enums\CollaboratorStatus;
use App\Enums\PayrollCycleStatus;
use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\PayrollCycle;
use App\Models\User;

describe('PayrollCycleController', function () {
    describe('index', function () {
        it('allows admin to GET /payroll-cycles', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->get('/payroll-cycles')
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('payroll-cycles/Index'));
        });

        it('redirects guest from /payroll-cycles', function () {
            $this->get('/payroll-cycles')->assertRedirect('/login');
        });

        it('forbids non-admin from /payroll-cycles', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->get('/payroll-cycles')
                ->assertForbidden();
        });
    });

    describe('store', function () {
        it('admin can POST /payroll-cycles with valid mes_referencia', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->post('/payroll-cycles', ['mes_referencia' => '2026-05'])
                ->assertRedirect();

            $this->assertDatabaseHas('payroll_cycles', [
                'mes_referencia' => '2026-05',
                'ano' => 2026,
                'mes' => 5,
            ]);
        });

        it('rejects duplicate mes_referencia with validation error', function () {
            $admin = User::factory()->admin()->create();
            PayrollCycle::factory()->forMonth(2026, 6)->create();

            $this->actingAs($admin)
                ->post('/payroll-cycles', ['mes_referencia' => '2026-06'])
                ->assertSessionHasErrors('mes_referencia');
        });

        it('rejects invalid mes_referencia format', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->post('/payroll-cycles', ['mes_referencia' => '2026-13'])
                ->assertSessionHasErrors('mes_referencia');
        });

        it('auto-creates entries for active collaborators', function () {
            $admin = User::factory()->admin()->create();
            $entity = LegalEntity::factory()->create();
            $collaborator = Collaborator::factory()->create([
                'legal_entity_id' => $entity->id,
                'status' => CollaboratorStatus::Ativo,
            ]);

            $this->actingAs($admin)
                ->post('/payroll-cycles', ['mes_referencia' => '2026-07']);

            $cycle = PayrollCycle::where('mes_referencia', '2026-07')->first();
            expect($cycle->entries()->where('collaborator_id', $collaborator->id)->exists())->toBeTrue();
        });
    });

    describe('show', function () {
        it('admin can GET /payroll-cycles/{id}', function () {
            $admin = User::factory()->admin()->create();
            $cycle = PayrollCycle::factory()->create();

            $this->actingAs($admin)
                ->get("/payroll-cycles/{$cycle->id}")
                ->assertOk()
                ->assertInertia(fn ($page) => $page->component('payroll-cycles/Show'));
        });

        it('returns 404 for non-existent cycle', function () {
            $admin = User::factory()->admin()->create();

            $this->actingAs($admin)
                ->get('/payroll-cycles/99999')
                ->assertNotFound();
        });
    });

    describe('update', function () {
        it('admin can PUT /payroll-cycles/{id} to advance status', function () {
            $admin = User::factory()->admin()->create();
            $cycle = PayrollCycle::factory()->create(['status' => PayrollCycleStatus::Aberto]);

            $this->actingAs($admin)
                ->put("/payroll-cycles/{$cycle->id}", ['status' => 'aguardando_nf_pj'])
                ->assertRedirect();

            $cycle->refresh();
            expect($cycle->status)->toBe(PayrollCycleStatus::AguardandoNfPj);
        });

        it('returns error for invalid status transition', function () {
            $admin = User::factory()->admin()->create();
            $cycle = PayrollCycle::factory()->create(['status' => PayrollCycleStatus::Aberto]);

            $this->actingAs($admin)
                ->put("/payroll-cycles/{$cycle->id}", ['status' => 'fechado'])
                ->assertSessionHasErrors('status');
        });
    });
});
