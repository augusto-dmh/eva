<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { DissidioRound } from '@/types/dissidio';

type Props = {
    rounds: DissidioRound[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dissídio', href: '/dissidio-rounds' }],
    },
});

function statusLabel(status: string): string {
    switch (status) {
        case 'rascunho':
            return 'Rascunho';
        case 'simulado':
            return 'Simulado';
        case 'aguardando_aprovacao':
            return 'Aguardando Aprovação';
        case 'aplicado':
            return 'Aplicado';
        case 'relatorio_gerado':
            return 'Relatório Gerado';
        default:
            return status;
    }
}

function statusClass(status: string): string {
    switch (status) {
        case 'rascunho':
            return 'bg-gray-100 text-gray-800';
        case 'simulado':
            return 'bg-blue-100 text-blue-800';
        case 'aguardando_aprovacao':
            return 'bg-blue-100 text-blue-800';
        case 'aplicado':
            return 'bg-green-100 text-green-800';
        case 'relatorio_gerado':
            return 'bg-teal-100 text-teal-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function formatPercent(value: number): string {
    return `${(value * 100).toFixed(2)}%`;
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    const [year, month, day] = value.split('-');

    return `${day}/${month}/${year}`;
}
</script>

<template>
    <Head title="Dissídio" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Rodadas de Dissídio</h1>
            <Button as-child>
                <Link href="/dissidio-rounds/create">Novo Dissídio</Link>
            </Button>
        </div>

        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Ano</th>
                        <th class="px-4 py-3 text-left font-medium">
                            Data Base
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Percentual
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">
                            Criado por
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="round in rounds"
                        :key="round.id"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ round.ano_referencia }}
                        </td>
                        <td class="px-4 py-3">
                            {{ formatDate(round.data_base) }}
                        </td>
                        <td class="px-4 py-3">
                            {{ formatPercent(round.percentual) }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusClass(round.status)"
                            >
                                {{ statusLabel(round.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ round.criado_por?.name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="`/dissidio-rounds/${round.id}`">
                                    Ver
                                </Link>
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="rounds.length === 0">
                        <td
                            colspan="6"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhuma rodada de dissídio encontrada.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
