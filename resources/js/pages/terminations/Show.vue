<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import TerminationAlert from '@/components/TerminationAlert.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { EnumOption } from '@/types/collaborator';
import type { TerminationRecord, TerminationStatus } from '@/types/termination';

type Props = {
    termination: TerminationRecord;
    allStatuses: EnumOption[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Colaboradores', href: '/collaborators' },
            { title: 'Rescisão', href: '#' },
        ],
    },
});

const STATUS_ORDER: TerminationStatus[] = [
    'iniciado',
    'simulacao_realizada',
    'previa_solicitada',
    'previa_conferida',
    'documentacao_enviada',
    'concluido',
];

const NEXT_STATUS: Record<TerminationStatus, TerminationStatus | null> = {
    iniciado: 'simulacao_realizada',
    simulacao_realizada: 'previa_solicitada',
    previa_solicitada: 'previa_conferida',
    previa_conferida: 'documentacao_enviada',
    documentacao_enviada: 'concluido',
    concluido: null,
};

const TERMINATION_TYPE_LABELS: Record<string, string> = {
    pedido_demissao: 'Pedido de Demissão',
    dispensa_sem_justa_causa: 'Dispensa Sem Justa Causa',
    dispensa_com_justa_causa: 'Dispensa Com Justa Causa',
    mutuo_acordo: 'Acordo Mútuo',
    termino_contrato: 'Término de Contrato',
};

function statusLabel(status: TerminationStatus): string {
    return props.allStatuses.find((s) => s.value === status)?.label ?? status;
}

function statusVariant(status: TerminationStatus) {
    if (status === 'concluido') {
        return 'default';
    }

    if (status === 'iniciado') {
        return 'secondary';
    }

    return 'outline';
}

function nextStatus(): TerminationStatus | null {
    return NEXT_STATUS[props.termination.status];
}

function advance() {
    const next = nextStatus();

    if (!next) {
        return;
    }

    router.put(`/termination-records/${props.termination.id}`, {
        status: next,
    });
}

function formatCurrency(value: string | number | null): string {
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
</script>

<template>
    <Head title="Rescisão" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold">
                    {{ termination.collaborator?.nome_completo ?? 'Rescisão' }}
                </h1>
                <Badge :variant="statusVariant(termination.status)">
                    {{ statusLabel(termination.status) }}
                </Badge>
                <Badge variant="outline">
                    {{
                        TERMINATION_TYPE_LABELS[
                            termination.tipo_desligamento
                        ] ?? termination.tipo_desligamento
                    }}
                </Badge>
            </div>
            <Button variant="outline" as-child>
                <Link
                    v-if="termination.collaborator"
                    :href="`/collaborators/${termination.collaborator.id}`"
                >
                    Voltar ao Colaborador
                </Link>
                <Link v-else href="/collaborators">Voltar</Link>
            </Button>
        </div>

        <!-- Flash Alert -->
        <TerminationAlert
            :termination-id="termination.id"
            :flash-cancelado="termination.flash_cancelado"
        />

        <!-- Status Stepper -->
        <Card>
            <CardHeader>
                <CardTitle>Progresso da Rescisão</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        v-for="(st, idx) in STATUS_ORDER"
                        :key="st"
                        class="flex items-center gap-2"
                    >
                        <span
                            :class="[
                                'rounded-full px-3 py-1 text-xs font-medium',
                                termination.status === st
                                    ? 'bg-primary text-primary-foreground'
                                    : STATUS_ORDER.indexOf(termination.status) >
                                        idx
                                      ? 'bg-green-600/20 text-green-400'
                                      : 'bg-muted text-muted-foreground',
                            ]"
                        >
                            {{ statusLabel(st) }}
                        </span>
                        <span
                            v-if="idx < STATUS_ORDER.length - 1"
                            class="text-muted-foreground"
                            >→</span
                        >
                    </div>
                </div>

                <div v-if="nextStatus()" class="mt-4">
                    <Button @click="advance">
                        Avançar para: {{ statusLabel(nextStatus()!) }}
                    </Button>
                </div>
                <div v-else class="mt-4 text-sm text-green-400">
                    Rescisão concluída.
                </div>
            </CardContent>
        </Card>

        <!-- Datas -->
        <Card>
            <CardHeader>
                <CardTitle>Datas</CardTitle>
            </CardHeader>
            <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <p class="text-sm text-muted-foreground">
                        Data de Comunicação
                    </p>
                    <p class="font-medium">
                        {{ formatDate(termination.data_comunicacao) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Data de Efetivação
                    </p>
                    <p class="font-medium">
                        {{ formatDate(termination.data_efetivacao) }}
                    </p>
                </div>
                <div v-if="termination.motivo">
                    <p class="text-sm text-muted-foreground">Motivo</p>
                    <p class="font-medium">{{ termination.motivo }}</p>
                </div>
            </CardContent>
        </Card>

        <!-- Financial Summary -->
        <Card>
            <CardHeader>
                <CardTitle>Resumo Financeiro da Rescisão</CardTitle>
            </CardHeader>
            <CardContent>
                <table class="w-full text-sm">
                    <tbody class="divide-y">
                        <tr>
                            <td class="py-2 text-muted-foreground">
                                Salário Proporcional ({{
                                    termination.salario_proporcional_dias
                                }}
                                dias)
                            </td>
                            <td class="py-2 text-right font-medium">
                                {{
                                    formatCurrency(
                                        termination.salario_proporcional_valor,
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">
                                Férias Proporcionais
                            </td>
                            <td class="py-2 text-right font-medium">
                                {{
                                    formatCurrency(
                                        termination.ferias_proporcionais_valor,
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">
                                1/3 de Férias
                            </td>
                            <td class="py-2 text-right font-medium">
                                {{
                                    formatCurrency(
                                        termination.terco_ferias_proporcionais,
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">
                                13° Proporcional
                            </td>
                            <td class="py-2 text-right font-medium">
                                {{
                                    formatCurrency(
                                        termination.decimo_terceiro_proporcional,
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">
                                Multa FGTS
                            </td>
                            <td class="py-2 text-right font-medium">
                                {{ formatCurrency(termination.multa_fgts) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">
                                Aviso Prévio
                            </td>
                            <td class="py-2 text-right font-medium">
                                {{
                                    formatCurrency(
                                        termination.aviso_previo_valor,
                                    )
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-muted-foreground">
                                Ajuste Flash
                            </td>
                            <td class="py-2 text-right font-medium">
                                {{
                                    formatCurrency(
                                        termination.ajuste_flash_valor,
                                    )
                                }}
                            </td>
                        </tr>
                        <tr class="border-t-2 font-bold">
                            <td class="py-2">Total da Rescisão</td>
                            <td class="py-2 text-right text-lg">
                                {{
                                    formatCurrency(
                                        termination.valor_total_rescisao,
                                    )
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <!-- Checklist -->
        <Card>
            <CardHeader>
                <CardTitle>Checklist Operacional</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'flex h-5 w-5 items-center justify-center rounded border-2',
                            termination.exame_demissional_agendado
                                ? 'border-green-500 bg-green-500 text-white'
                                : 'border-gray-300',
                        ]"
                    >
                        <svg
                            v-if="termination.exame_demissional_agendado"
                            class="h-3 w-3"
                            viewBox="0 0 12 12"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M2 6l3 3 5-5" />
                        </svg>
                    </span>
                    <span class="text-sm">Exame Demissional Agendado</span>
                    <span
                        v-if="termination.exame_demissional_data"
                        class="text-xs text-muted-foreground"
                    >
                        ({{ formatDate(termination.exame_demissional_data) }})
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'flex h-5 w-5 items-center justify-center rounded border-2',
                            termination.previa_contabilidade_solicitada
                                ? 'border-green-500 bg-green-500 text-white'
                                : 'border-gray-300',
                        ]"
                    >
                        <svg
                            v-if="termination.previa_contabilidade_solicitada"
                            class="h-3 w-3"
                            viewBox="0 0 12 12"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M2 6l3 3 5-5" />
                        </svg>
                    </span>
                    <span class="text-sm">Prévia Contabilidade Solicitada</span>
                </div>

                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'flex h-5 w-5 items-center justify-center rounded border-2',
                            termination.previa_contabilidade_conferida
                                ? 'border-green-500 bg-green-500 text-white'
                                : 'border-gray-300',
                        ]"
                    >
                        <svg
                            v-if="termination.previa_contabilidade_conferida"
                            class="h-3 w-3"
                            viewBox="0 0 12 12"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M2 6l3 3 5-5" />
                        </svg>
                    </span>
                    <span class="text-sm">Prévia Contabilidade Conferida</span>
                </div>

                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'flex h-5 w-5 items-center justify-center rounded border-2',
                            termination.documentos_enviados_rh
                                ? 'border-green-500 bg-green-500 text-white'
                                : 'border-gray-300',
                        ]"
                    >
                        <svg
                            v-if="termination.documentos_enviados_rh"
                            class="h-3 w-3"
                            viewBox="0 0 12 12"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M2 6l3 3 5-5" />
                        </svg>
                    </span>
                    <span class="text-sm">Documentos Enviados ao RH</span>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
