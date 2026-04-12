<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { PlrRound } from '@/types/plr';

type Props = {
    rounds: PlrRound[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'PLR', href: '/plr' }],
    },
});

function statusLabel(status: string): string {
    switch (status) {
        case 'rascunho':
            return 'Rascunho';
        case 'documento_enviado':
            return 'Documento Enviado';
        case 'comite_criado':
            return 'Comitê Criado';
        case 'aguardando_sindicato':
            return 'Aguardando Sindicato';
        case 'aprovado':
            return 'Aprovado';
        case 'simulado':
            return 'Simulado';
        case 'pago':
            return 'Pago';
        default:
            return status;
    }
}

function statusClass(status: string): string {
    switch (status) {
        case 'rascunho':
            return 'bg-gray-100 text-gray-800';
        case 'documento_enviado':
            return 'bg-blue-100 text-blue-800';
        case 'comite_criado':
            return 'bg-indigo-100 text-indigo-800';
        case 'aguardando_sindicato':
            return 'bg-yellow-100 text-yellow-800';
        case 'aprovado':
            return 'bg-teal-100 text-teal-800';
        case 'simulado':
            return 'bg-purple-100 text-purple-800';
        case 'pago':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function formatCurrency(value: number | null): string {
    if (value === null || value === undefined) {
        return '—';
    }

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}
</script>

<template>
    <Head title="PLR" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">PLR</h1>
            <Button as-child>
                <Link href="/plr/create">Nova PLR</Link>
            </Button>
        </div>

        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Ano</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Total Distribuído
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
                        <td class="px-4 py-3 text-right">
                            {{ formatCurrency(round.valor_total_distribuido) }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ round.criado_por?.name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="`/plr/${round.id}`"> Ver </Link>
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="rounds.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhuma rodada de PLR encontrada.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
