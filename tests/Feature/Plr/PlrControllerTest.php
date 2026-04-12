<?php

use App\Models\PlrRound;
use App\Models\User;

describe('PlrController', function () {
    it('admin can GET /plr', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/plr')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('plr/Index'));
    });

    it('admin can POST to create PLR round', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/plr', ['ano_referencia' => 2025])
            ->assertRedirect();

        $this->assertDatabaseHas('plr_rounds', ['ano_referencia' => 2025]);
    });

    it('admin can GET /plr/{id}', function () {
        $admin = User::factory()->admin()->create();
        $round = PlrRound::factory()->create(['criado_por_id' => $admin->id]);

        $this->actingAs($admin)
            ->get("/plr/{$round->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('plr/Show'));
    });

    it('admin can simulate PLR with a total amount', function () {
        $admin = User::factory()->admin()->create();
        $round = PlrRound::factory()->rascunho()->create(['criado_por_id' => $admin->id]);

        $this->actingAs($admin)
            ->post("/plr/{$round->id}/simulate", ['valor_total' => 50000.00])
            ->assertRedirect();
    });

    it('redirects guest', function () {
        $this->get('/plr')->assertRedirect('/login');
    });
});
