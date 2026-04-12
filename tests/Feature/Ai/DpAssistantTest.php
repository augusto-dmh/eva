<?php

use App\Ai\Agents\DpAssistantAgent;
use App\Models\User;
use Laravel\Ai\Ai;

describe('DpAssistantController', function () {
    it('returns an answer from the DP assistant', function () {
        $admin = User::factory()->admin()->create();

        Ai::fakeAgent(DpAssistantAgent::class, [
            'Existem 5 colaboradores CLT ativos no momento.',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/dp-assistant/ask', [
                'question' => 'Quantos colaboradores CLT temos?',
            ])
            ->assertOk()
            ->assertJsonStructure(['answer']);

        expect($response->json('answer'))->toContain('CLT');
    });

    it('validates the question field', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->postJson('/dp-assistant/ask', ['question' => ''])
            ->assertUnprocessable();
    });

    it('redirects guest', function () {
        $this->postJson('/dp-assistant/ask', ['question' => 'test'])
            ->assertUnauthorized();
    });
});
