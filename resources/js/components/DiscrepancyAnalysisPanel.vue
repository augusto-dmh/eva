<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{ cycleId: number }>();

const accountingInput = ref('');
const report = ref('');
const loading = ref(false);
const error = ref('');

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function analyze() {
    if (!accountingInput.value.trim()) {
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        let accountingData: Record<string, unknown>;

        try {
            accountingData = JSON.parse(accountingInput.value);
        } catch {
            error.value =
                'JSON inválido. Insira os dados contábeis em formato JSON.';
            loading.value = false;

            return;
        }

        const response = await fetch(
            `/payroll-cycles/${props.cycleId}/discrepancy-analysis`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ accounting_data: accountingData }),
            },
        );
        const data = await response.json();
        report.value = data.report;
    } catch {
        error.value =
            'Erro ao analisar discrepâncias. Verifique a configuração da API.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="rounded-xl border border-white/10 bg-white/5 p-6">
        <h3 class="mb-4 text-lg font-semibold text-white">
            Análise de Discrepâncias (AI)
        </h3>
        <textarea
            v-model="accountingInput"
            rows="4"
            placeholder='{"holding": {"total": 50000}, "educacao": {"total": 30000}}'
            class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-white placeholder-white/40 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
        <button
            :disabled="loading || !accountingInput.trim()"
            class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500 disabled:opacity-50"
            @click="analyze"
        >
            {{ loading ? 'Analisando...' : 'Analisar com IA' }}
        </button>
        <div
            v-if="report"
            class="mt-4 rounded-lg bg-white/5 p-4 text-sm whitespace-pre-wrap text-white/80"
        >
            {{ report }}
        </div>
        <div
            v-if="error"
            class="mt-4 rounded-lg bg-red-500/10 p-4 text-sm text-red-400"
        >
            {{ error }}
        </div>
    </div>
</template>
