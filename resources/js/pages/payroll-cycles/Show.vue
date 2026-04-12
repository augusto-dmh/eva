<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CycleStatusStepper from '@/components/CycleStatusStepper.vue';
import PayrollEntryRow from '@/components/PayrollEntryRow.vue';
import type { PayrollCycle, PayrollCycleStatus } from '@/types/payroll';

type Props = {
    cycle: PayrollCycle;
    allStatuses: { value: string; label: string }[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Folha de Pagamento', href: '/payroll-cycles' },
            { title: 'Detalhes', href: '#' },
        ],
    },
});

function statusClass(status: PayrollCycleStatus) {
    switch (status) {
        case 'aberto':
            return 'bg-blue-100 text-blue-800';
        case 'aguardando_nf_pj':
            return 'bg-yellow-100 text-yellow-800';
        case 'aguardando_comissoes':
            return 'bg-orange-100 text-orange-800';
        case 'em_revisao':
            return 'bg-purple-100 text-purple-800';
        case 'conferido_contabilidade':
            return 'bg-teal-100 text-teal-800';
        case 'fechado':
            return 'bg-green-100 text-green-800';
    }
}

function statusLabel(status: PayrollCycleStatus) {
    switch (status) {
        case 'aberto':
            return 'Aberto';
        case 'aguardando_nf_pj':
            return 'Aguardando NF PJ';
        case 'aguardando_comissoes':
            return 'Aguardando Comissões';
        case 'em_revisao':
            return 'Em Revisão';
        case 'conferido_contabilidade':
            return 'Conferido Contabilidade';
        case 'fechado':
            return 'Fechado';
    }
}

function formatCurrency(value: string | null) {
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

function formatDate(value: string | null) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('pt-BR');
}
</script>

<template>
    <Head :title="`Folha ${cycle.mes_referencia}`" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold">Folha {{ cycle.mes_referencia }}</h1>
            <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="statusClass(cycle.status)"
            >
                {{ statusLabel(cycle.status) }}
            </span>
        </div>

        <div class="text-sm text-muted-foreground">
            Abertura: {{ formatDate(cycle.data_abertura) }}
            <span v-if="cycle.data_fechamento">
                &nbsp;·&nbsp; Fechamento:
                {{ formatDate(cycle.data_fechamento) }}
            </span>
        </div>

        <!-- Stepper -->
        <div class="rounded-lg border p-4">
            <CycleStatusStepper :cycle="cycle" :all-statuses="allStatuses" />
        </div>

        <!-- Totals -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
            <div class="rounded-lg border p-4">
                <div class="text-xs text-muted-foreground">Salários Brutos</div>
                <div class="mt-1 text-lg font-semibold">
                    {{ formatCurrency(cycle.salarios_brutos) }}
                </div>
            </div>
            <div class="rounded-lg border p-4">
                <div class="text-xs text-muted-foreground">Comissões</div>
                <div class="mt-1 text-lg font-semibold">
                    {{ formatCurrency(cycle.comissoes) }}
                </div>
            </div>
            <div class="rounded-lg border p-4">
                <div class="text-xs text-muted-foreground">Deduções</div>
                <div class="mt-1 text-lg font-semibold">
                    {{ formatCurrency(cycle.deducoes) }}
                </div>
            </div>
            <div class="rounded-lg border p-4">
                <div class="text-xs text-muted-foreground">Líquido</div>
                <div class="mt-1 text-lg font-semibold">
                    {{ formatCurrency(cycle.liquido) }}
                </div>
            </div>
            <div class="rounded-lg border p-4">
                <div class="text-xs text-muted-foreground">PJ</div>
                <div class="mt-1 text-lg font-semibold">
                    {{ formatCurrency(cycle.pj) }}
                </div>
            </div>
        </div>

        <!-- Entries table -->
        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">
                            Colaborador
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Contrato
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Empresa</th>
                        <th class="px-4 py-3 text-right font-medium">Bruto</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Líquido
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <template
                        v-for="entry in cycle.entries ?? []"
                        :key="entry.id"
                    >
                        <PayrollEntryRow :entry="entry" :cycle-id="cycle.id" />
                    </template>
                    <tr v-if="!cycle.entries || cycle.entries.length === 0">
                        <td
                            colspan="7"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhuma entrada encontrada.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
