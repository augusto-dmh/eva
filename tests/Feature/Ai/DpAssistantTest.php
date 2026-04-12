<?php

use App\Ai\Agents\DpAssistantAgent;
use App\Models\User;
use Laravel\Ai\Ai;

describe('DpAssistantController', function () {
    it('returns an answer with conversation_id from the DP assistant', function () {
        $admin = User::factory()->admin()->create();

        Ai::fakeAgent(DpAssistantAgent::class, [
            'Existem 5 colaboradores CLT ativos no momento.',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/dp-assistant/ask', [
                'question' => 'Quantos colaboradores CLT temos?',
            ])
            ->assertOk()
            ->assertJsonStructure(['answer', 'conversation_id']);

        expect($response->json('answer'))->toContain('CLT');
    });

    it('accepts a conversation_id for multi-turn conversations', function () {
        $admin = User::factory()->admin()->create();

        Ai::fakeAgent(DpAssistantAgent::class, [
            'Existem 5 colaboradores CLT ativos no momento.',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/dp-assistant/ask', [
                'question' => 'Quantos CLT?',
                'conversation_id' => null,
            ])
            ->assertOk()
            ->assertJsonStructure(['answer', 'conversation_id']);

        $conversationId = $response->json('conversation_id');

        Ai::fakeAgent(DpAssistantAgent::class, [
            'Desses 5, 3 são do departamento de tecnologia.',
        ]);

        $this->actingAs($admin)
            ->postJson('/dp-assistant/ask', [
                'question' => 'Quais desses são de tecnologia?',
                'conversation_id' => $conversationId,
            ])
            ->assertOk()
            ->assertJsonStructure(['answer', 'conversation_id']);
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
