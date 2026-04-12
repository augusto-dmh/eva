<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { DissidioRound } from '@/types/dissidio';

type Props = {
    round: DissidioRound;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dissídio', href: '/dissidio-rounds' },
            { title: 'Detalhes', href: '#' },
        ],
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

function statusVariant(status: string) {
    switch (status) {
        case 'aplicado':
            return 'default' as const;
        case 'rascunho':
            return 'secondary' as const;
        default:
            return 'outline' as const;
    }
}

function formatPercent(value: number): string {
    return `${(value * 100).toFixed(2)}%`;
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

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    const [year, month, day] = value.split('-');

    return `${day}/${month}/${year}`;
}

function simulate() {
    router.post(`/dissidio-rounds/${props.round.id}/simulate`);
}

function apply() {
    router.post(`/dissidio-rounds/${props.round.id}/apply`);
}
</script>

<template>
    <Head title="Dissídio" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold">
                    Dissídio {{ round.ano_referencia }}
                </h1>
                <Badge :variant="statusVariant(round.status)">
                    {{ statusLabel(round.status) }}
                </Badge>
            </div>
            <Button variant="outline" as-child>
                <Link href="/dissidio-rounds">Voltar</Link>
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
                    <p class="text-sm text-muted-foreground">Data Base</p>
                    <p class="font-medium">{{ formatDate(round.data_base) }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Percentual</p>
                    <p class="text-lg font-medium">
                        {{ formatPercent(round.percentual) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Aplica a Estagiários
                    </p>
                    <p class="font-medium">
                        {{ round.aplica_estagiarios ? 'Sim' : 'Não' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Criado por</p>
                    <p class="font-medium">
                        {{ round.criado_por?.name ?? '—' }}
                    </p>
                </div>
                <div v-if="round.aplicado_por">
                    <p class="text-sm text-muted-foreground">Aplicado por</p>
                    <p class="font-medium">{{ round.aplicado_por.name }}</p>
                </div>
                <div v-if="round.aplicado_em">
                    <p class="text-sm text-muted-foreground">Aplicado em</p>
                    <p class="font-medium">
                        {{ formatDate(round.aplicado_em) }}
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
                <Button v-if="round.status === 'rascunho'" @click="simulate">
                    Simular
                </Button>
                <Button
                    v-if="
                        round.status === 'simulado' ||
                        round.status === 'aguardando_aprovacao'
                    "
                    @click="apply"
                >
                    Aplicar Dissídio
                </Button>
                <p
                    v-if="round.status === 'aplicado'"
                    class="text-sm text-green-600"
                >
                    Dissídio aplicado com sucesso.
                </p>
            </CardContent>
        </Card>

        <!-- Entries Table -->
        <Card
            v-if="
                round.status !== 'rascunho' &&
                round.entries &&
                round.entries.length > 0
            "
        >
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
                                    Salário Anterior
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Salário Novo
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Diferença Retroativa
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Meses Retroativos
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
                                    {{ formatCurrency(entry.salario_anterior) }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{ formatCurrency(entry.salario_novo) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{
                                        formatCurrency(
                                            entry.diferenca_retroativa,
                                        )
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ entry.meses_retroativos }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
