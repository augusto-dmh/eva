<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type {
    VacationBatch,
    VacationBatchCollaborator,
    VacationBatchStatus,
} from '@/types/vacation';

type Props = {
    batch: VacationBatch;
    eligible: VacationBatchCollaborator[];
    ineligible: VacationBatchCollaborator[];
    allStatuses: { value: string; label: string }[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Férias', href: '/vacation-batches' },
            { title: 'Detalhes', href: '#' },
        ],
    },
});

const NEXT_STATUS: Record<VacationBatchStatus, VacationBatchStatus | null> = {
    rascunho: 'calculado',
    calculado: 'em_revisao',
    em_revisao: 'confirmado',
    confirmado: 'concluido',
    concluido: null,
};

function nextStatus(): VacationBatchStatus | null {
    return NEXT_STATUS[props.batch.status];
}

function nextStatusLabel(): string {
    const next = nextStatus();

    if (!next) {
        return '';
    }

    const found = props.allStatuses.find((s) => s.value === next);

    return found ? found.label : next;
}

function advanceStatus() {
    const next = nextStatus();

    if (!next) {
        return;
    }

    router.put(
        `/vacation-batches/${props.batch.id}`,
        { status: next },
        { preserveScroll: true },
    );
}

function tipoLabel(): string {
    switch (props.batch.tipo) {
        case 'clt':
            return 'CLT';
        case 'estagiario':
            return 'Estagiário';
    }
}

function tipoClass(): string {
    switch (props.batch.tipo) {
        case 'clt':
            return 'bg-blue-100 text-blue-800';
        case 'estagiario':
            return 'bg-purple-100 text-purple-800';
    }
}

function statusLabel(status: VacationBatchStatus): string {
    switch (status) {
        case 'rascunho':
            return 'Rascunho';
        case 'calculado':
            return 'Calculado';
        case 'em_revisao':
            return 'Em Revisão';
        case 'confirmado':
            return 'Confirmado';
        case 'concluido':
            return 'Concluído';
    }
}

function statusClass(status: VacationBatchStatus): string {
    switch (status) {
        case 'rascunho':
            return 'bg-gray-100 text-gray-800';
        case 'calculado':
            return 'bg-blue-100 text-blue-800';
        case 'em_revisao':
            return 'bg-blue-100 text-blue-800';
        case 'confirmado':
            return 'bg-teal-100 text-teal-800';
        case 'concluido':
            return 'bg-green-100 text-green-800';
    }
}

function formatCurrency(value: string | null): string {
    if (!value) {
        return '—';
    }

    const num = parseFloat(value);

    if (isNaN(num)) {
        return value;
    }

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(num);
}
</script>

<template>
    <Head :title="`Férias ${batch.mes_referencia}`" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold">
                Férias {{ batch.mes_referencia }}
            </h1>
            <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="tipoClass()"
            >
                {{ tipoLabel() }}
            </span>
            <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="statusClass(batch.status)"
            >
                {{ statusLabel(batch.status) }}
            </span>
        </div>

        <!-- Status advance button -->
        <div v-if="nextStatus()" class="flex items-center gap-3">
            <Button @click="advanceStatus"
                >Avançar para {{ nextStatusLabel() }}</Button
            >
        </div>

        <!-- Eligible collaborators -->
        <div class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">
                Colaboradores Elegíveis ({{ eligible.length }})
            </h2>
            <div class="overflow-x-auto rounded-lg border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">
                                Nome
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Meses Acumulados
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Valor Férias
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                1/3 Constitucional
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="item in eligible"
                            :key="item.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-4 py-3">
                                {{ item.collaborator?.nome_completo ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ item.meses_acumulados }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ formatCurrency(item.valor_ferias) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{
                                    formatCurrency(
                                        item.valor_terco_constitucional,
                                    )
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="eligible.length === 0">
                            <td
                                colspan="5"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                <template v-if="batch.status === 'rascunho'">
                                    Clique em Calcular para processar
                                    elegibilidade
                                </template>
                                <template v-else>
                                    Nenhum colaborador elegível encontrado.
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ineligible collaborators -->
        <div class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">
                Colaboradores Não Elegíveis ({{ ineligible.length }})
            </h2>
            <div class="overflow-x-auto rounded-lg border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">
                                Nome
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Meses Acumulados
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Motivo
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="item in ineligible"
                            :key="item.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-4 py-3">
                                {{ item.collaborator?.nome_completo ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ item.meses_acumulados }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                Tempo de empresa insuficiente
                            </td>
                        </tr>
                        <tr v-if="ineligible.length === 0">
                            <td
                                colspan="3"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                <template v-if="batch.status === 'rascunho'">
                                    Clique em Calcular para processar
                                    elegibilidade
                                </template>
                                <template v-else>
                                    Nenhum colaborador não elegível encontrado.
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
