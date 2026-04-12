<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import CycleStatusStepper from '@/components/CycleStatusStepper.vue';
import DiscrepancyAnalysisPanel from '@/components/DiscrepancyAnalysisPanel.vue';
import PayrollEntryRow from '@/components/PayrollEntryRow.vue';
import { Button } from '@/components/ui/button';
import type { PayrollCycle, PayrollCycleStatus } from '@/types/payroll';
import type { PjInvoice, PjInvoiceStatus } from '@/types/pj-invoice';

type Props = {
    cycle: PayrollCycle;
    allStatuses: { value: string; label: string }[];
};

const props = defineProps<Props>();

const pjInvoices = ref<PjInvoice[]>(
    (props.cycle as PayrollCycle & { pjInvoices?: PjInvoice[] }).pjInvoices ??
        [],
);

function pjInvoiceStatusClass(status: PjInvoiceStatus): string {
    switch (status) {
        case 'pendente':
            return 'bg-yellow-100 text-yellow-800';
        case 'recebida':
            return 'bg-blue-100 text-blue-800';
        case 'em_revisao':
            return 'bg-purple-100 text-purple-800';
        case 'aprovada':
            return 'bg-green-100 text-green-800';
        case 'rejeitada':
            return 'bg-red-100 text-red-800';
    }
}

function pjInvoiceStatusLabel(status: PjInvoiceStatus): string {
    switch (status) {
        case 'pendente':
            return 'Pendente';
        case 'recebida':
            return 'Recebida';
        case 'em_revisao':
            return 'Em Revisão';
        case 'aprovada':
            return 'Aprovada';
        case 'rejeitada':
            return 'Rejeitada';
    }
}

const invoiceEditState = ref<
    Record<number, { status: string; observacoes: string }>
>({});

function startEdit(invoice: PjInvoice) {
    invoiceEditState.value[invoice.id] = {
        status: invoice.status,
        observacoes: invoice.observacoes ?? '',
    };
}

function saveInvoice(invoice: PjInvoice) {
    const state = invoiceEditState.value[invoice.id];

    if (!state) {
        return;
    }

    router.put(
        `/pj-invoices/${invoice.id}`,
        {
            status: state.status,
            observacoes: state.observacoes,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                delete invoiceEditState.value[invoice.id];
            },
        },
    );
}

async function downloadInvoice(invoice: PjInvoice) {
    const response = await fetch(`/pj-invoices/${invoice.id}/signed-url`);
    const data = await response.json();
    window.open(data.url, '_blank');
}

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

        <!-- AI Discrepancy Analysis -->
        <DiscrepancyAnalysisPanel :cycle-id="cycle.id" />

        <!-- PJ Invoices section -->
        <div v-if="pjInvoices.length > 0" class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold">Notas Fiscais PJ</h2>
            <div class="overflow-x-auto rounded-lg border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">
                                Colaborador
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Nº Nota
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Valor
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Observações
                            </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="invoice in pjInvoices"
                            :key="invoice.id"
                            class="border-t"
                        >
                            <td class="px-4 py-3">
                                {{ invoice.collaborator?.nome_completo ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ invoice.numero_nota }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ formatCurrency(invoice.valor) }}
                            </td>
                            <td class="px-4 py-3">
                                <template v-if="invoiceEditState[invoice.id]">
                                    <select
                                        v-model="
                                            invoiceEditState[invoice.id].status
                                        "
                                        class="rounded border px-2 py-1 text-sm"
                                    >
                                        <option value="pendente">
                                            Pendente
                                        </option>
                                        <option value="recebida">
                                            Recebida
                                        </option>
                                        <option value="em_revisao">
                                            Em Revisão
                                        </option>
                                        <option value="aprovada">
                                            Aprovada
                                        </option>
                                        <option value="rejeitada">
                                            Rejeitada
                                        </option>
                                    </select>
                                </template>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="
                                        pjInvoiceStatusClass(invoice.status)
                                    "
                                >
                                    {{ pjInvoiceStatusLabel(invoice.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <template v-if="invoiceEditState[invoice.id]">
                                    <input
                                        v-model="
                                            invoiceEditState[invoice.id]
                                                .observacoes
                                        "
                                        class="w-full rounded border px-2 py-1 text-sm"
                                        placeholder="Observações"
                                    />
                                </template>
                                <span v-else class="text-muted-foreground">
                                    {{ invoice.observacoes ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <template
                                        v-if="invoiceEditState[invoice.id]"
                                    >
                                        <Button
                                            size="sm"
                                            @click="saveInvoice(invoice)"
                                        >
                                            Salvar
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            @click="
                                                delete invoiceEditState[
                                                    invoice.id
                                                ]
                                            "
                                        >
                                            Cancelar
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="startEdit(invoice)"
                                        >
                                            Editar
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            @click="downloadInvoice(invoice)"
                                        >
                                            Download
                                        </Button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
