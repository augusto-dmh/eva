<?php

use App\Models\ThirteenthSalaryRound;
use App\Models\User;

describe('ThirteenthSalaryController', function () {
    it('admin can GET /thirteenth-salary', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/thirteenth-salary')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('thirteenth-salary/Index'));
    });

    it('admin can POST to create round', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/thirteenth-salary', [
                'ano_referencia' => 2025,
                'primeira_parcela_data_limite' => '2025-11-30',
                'segunda_parcela_data_limite' => '2025-12-20',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('thirteenth_salary_rounds', ['ano_referencia' => 2025]);
    });

    it('admin can GET /thirteenth-salary/{id}', function () {
        $admin = User::factory()->admin()->create();
        $round = ThirteenthSalaryRound::factory()->create(['criado_por_id' => $admin->id]);

        $this->actingAs($admin)
            ->get("/thirteenth-salary/{$round->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('thirteenth-salary/Show'));
    });

    it('admin can simulate', function () {
        $admin = User::factory()->admin()->create();
        $round = ThirteenthSalaryRound::factory()->create(['criado_por_id' => $admin->id]);

        $this->actingAs($admin)
            ->post("/thirteenth-salary/{$round->id}/simulate")
            ->assertRedirect();

        expect($round->fresh()->status->value)->toBe('primeira_parcela_simulada');
    });

    it('redirects guest', function () {
        $this->get('/thirteenth-salary')->assertRedirect('/login');
    });
});
