<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { ThirteenthSalaryRound } from '@/types/thirteenth-salary';

type Props = {
    rounds: ThirteenthSalaryRound[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: '13º Salário', href: '/thirteenth-salary' }],
    },
});

function statusLabel(status: string): string {
    switch (status) {
        case 'aberto':
            return 'Aberto';
        case 'primeira_parcela_simulada':
            return '1ª Parcela Simulada';
        case 'primeira_parcela_paga':
            return '1ª Parcela Paga';
        case 'segunda_parcela_simulada':
            return '2ª Parcela Simulada';
        case 'segunda_parcela_paga':
            return '2ª Parcela Paga';
        case 'concluido':
            return 'Concluído';
        default:
            return status;
    }
}

function statusClass(status: string): string {
    switch (status) {
        case 'aberto':
            return 'bg-gray-100 text-gray-800';
        case 'primeira_parcela_simulada':
            return 'bg-blue-100 text-blue-800';
        case 'primeira_parcela_paga':
            return 'bg-yellow-100 text-yellow-800';
        case 'segunda_parcela_simulada':
            return 'bg-purple-100 text-purple-800';
        case 'segunda_parcela_paga':
            return 'bg-orange-100 text-orange-800';
        case 'concluido':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
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
    <Head title="13º Salário" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">13º Salário</h1>
            <Button as-child>
                <Link href="/thirteenth-salary/create">Novo 13º Salário</Link>
            </Button>
        </div>

        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Ano</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">
                            1ª Parcela até
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            2ª Parcela até
                        </th>
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
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusClass(round.status)"
                            >
                                {{ statusLabel(round.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ formatDate(round.primeira_parcela_data_limite) }}
                        </td>
                        <td class="px-4 py-3">
                            {{ formatDate(round.segunda_parcela_data_limite) }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ round.criado_por?.name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="`/thirteenth-salary/${round.id}`">
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
                            Nenhuma rodada de 13º salário encontrada.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
