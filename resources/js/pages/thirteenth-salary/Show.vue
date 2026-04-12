<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { ThirteenthSalaryRound } from '@/types/thirteenth-salary';

type Props = {
    round: ThirteenthSalaryRound;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: '13º Salário', href: '/thirteenth-salary' },
            { title: 'Detalhes', href: '#' },
        ],
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

function statusVariant(status: string) {
    switch (status) {
        case 'concluido':
            return 'default' as const;
        case 'aberto':
            return 'secondary' as const;
        default:
            return 'outline' as const;
    }
}

function formatDate(value: string | null): string {
    if (!value) return '—';
    const dateOnly = value.split('T')[0];
    const [year, month, day] = dateOnly.split('-');
    return `${day}/${month}/${year}`;
}

function formatCurrency(value: number | string | null): string {
    if (value === null || value === undefined) {
        return '—';
    }

    const num = parseFloat(String(value));

    if (isNaN(num)) {
        return String(value);
    }

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(num);
}

function simulate() {
    router.post(`/thirteenth-salary/${props.round.id}/simulate`);
}
</script>

<template>
    <Head title="13º Salário" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold">
                    13º Salário {{ round.ano_referencia }}
                </h1>
                <Badge :variant="statusVariant(round.status)">
                    {{ statusLabel(round.status) }}
                </Badge>
            </div>
            <Button variant="outline" as-child>
                <Link href="/thirteenth-salary">Voltar</Link>
            </Button>
        </div>

        <!-- Round Details -->
        <Card>
            <CardHeader>
                <CardTitle>Detalhes da Rodada</CardTitle>
            </CardHeader>
            <CardContent
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p class="text-sm text-muted-foreground">
                        Ano de Referência
                    </p>
                    <p class="font-medium">{{ round.ano_referencia }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        1ª Parcela — Data Limite
                    </p>
                    <p class="font-medium">
                        {{ formatDate(round.primeira_parcela_data_limite) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        2ª Parcela — Data Limite
                    </p>
                    <p class="font-medium">
                        {{ formatDate(round.segunda_parcela_data_limite) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Criado por</p>
                    <p class="font-medium">
                        {{ round.criado_por?.name ?? '—' }}
                    </p>
                </div>
                <div
                    v-if="round.observacoes"
                    class="md:col-span-2 lg:col-span-3"
                >
                    <p class="text-sm text-muted-foreground">Observações</p>
                    <p class="font-medium">{{ round.observacoes }}</p>
                </div>
            </CardContent>
        </Card>

        <!-- Actions -->
        <Card>
            <CardHeader>
                <CardTitle>Ações</CardTitle>
            </CardHeader>
            <CardContent class="flex gap-3">
                <Button v-if="round.status === 'aberto'" @click="simulate">
                    Simular
                </Button>
                <p
                    v-if="round.status === 'concluido'"
                    class="text-sm text-green-600"
                >
                    13º salário concluído.
                </p>
            </CardContent>
        </Card>

        <!-- Entries Table -->
        <Card v-if="round.entries && round.entries.length > 0">
            <CardHeader>
                <CardTitle>
                    Colaboradores ({{ round.entries.length }})
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">
                                    Colaborador
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Meses
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Valor Integral
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    1ª Parcela
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    INSS
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    IRRF
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    2ª Parcela
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="entry in round.entries"
                                :key="entry.id"
                                class="hover:bg-muted/30"
                            >
                                <td class="px-4 py-3">
                                    {{
                                        entry.collaborator?.nome_completo ?? '—'
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ entry.meses_trabalhados }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{ formatCurrency(entry.valor_integral) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{
                                        formatCurrency(
                                            entry.primeira_parcela_valor,
                                        )
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-destructive"
                                >
                                    {{ formatCurrency(entry.desconto_inss) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-destructive"
                                >
                                    {{ formatCurrency(entry.desconto_irrf) }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{
                                        formatCurrency(
                                            entry.segunda_parcela_valor,
                                        )
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
