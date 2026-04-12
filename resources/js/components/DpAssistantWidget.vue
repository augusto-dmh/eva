<script setup lang="ts">
import { ref } from 'vue';

const question = ref('');
const answer = ref('');
const loading = ref(false);
const error = ref('');

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function ask() {
    if (!question.value.trim()) {
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/dp-assistant/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ question: question.value }),
        });
        const data = await response.json();
        answer.value = data.answer;
    } catch {
        error.value =
            'Erro ao consultar o assistente. Verifique a configuração da API.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="rounded-xl border border-white/10 bg-white/5 p-6">
        <h3 class="mb-4 text-lg font-semibold text-white">Assistente DP</h3>
        <div class="flex gap-2">
            <input
                v-model="question"
                type="text"
                placeholder="Pergunte sobre a folha, colaboradores, férias..."
                class="flex-1 rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-white placeholder-white/40 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                @keyup.enter="ask"
            />
            <button
                :disabled="loading || !question.trim()"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500 disabled:opacity-50"
                @click="ask"
            >
                {{ loading ? '...' : 'Perguntar' }}
            </button>
        </div>
        <div
            v-if="answer"
            class="mt-4 rounded-lg bg-white/5 p-4 text-sm whitespace-pre-wrap text-white/80"
        >
            {{ answer }}
        </div>
        <div
            v-if="error"
            class="mt-4 rounded-lg bg-red-500/10 p-4 text-sm text-red-400"
        >
            {{ error }}
        </div>
    </div>
</template>
