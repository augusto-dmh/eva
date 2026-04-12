<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import InvoiceUpload from '@/components/InvoiceUpload.vue';
import type { Collaborator } from '@/types/collaborator';
import type { PayrollCycle } from '@/types/payroll';
import type { PjInvoice, PjInvoiceStatus } from '@/types/pj-invoice';

type Props = {
    invoices: PjInvoice[];
    activeCycle: PayrollCycle | null;
    collaborator: Collaborator;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Notas Fiscais', href: '/self-service/invoices' },
        ],
    },
});

const invoiceForActiveCycle = computed(() => {
    if (!props.activeCycle) {
        return null;
    }

    return (
        props.invoices.find(
            (inv) => inv.payroll_cycle_id === props.activeCycle!.id,
        ) ?? null
    );
});

function statusClass(status: PjInvoiceStatus): string {
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

function statusLabel(status: PjInvoiceStatus): string {
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

function formatCurrency(value: string): string {
    const num = parseFloat(value);

    if (isNaN(num)) {
        return value;
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
    <Head title="Minhas Notas Fiscais" />

    <div class="flex flex-col gap-6 p-6">
        <h1 class="text-2xl font-bold">Minhas Notas Fiscais</h1>

        <!-- Upload form for active cycle -->
        <div v-if="activeCycle" class="rounded-lg border p-6">
            <div v-if="invoiceForActiveCycle">
                <p class="text-muted-foreground">
                    Nota enviada para
                    <strong>{{ activeCycle.mes_referencia }}</strong>
                </p>
                <span
                    class="mt-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="statusClass(invoiceForActiveCycle.status)"
                >
                    {{ statusLabel(invoiceForActiveCycle.status) }}
                </span>
            </div>
            <InvoiceUpload
                v-else
                upload-url="/self-service/invoices"
                :cycle-mes-referencia="activeCycle.mes_referencia"
            />
        </div>

        <div
            v-else
            class="rounded-lg border p-6 text-center text-muted-foreground"
        >
            Não há ciclo aguardando nota fiscal no momento.
        </div>

        <!-- Past invoices table -->
        <div
            v-if="invoices.length > 0"
            class="overflow-x-auto rounded-lg border"
        >
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Ciclo</th>
                        <th class="px-4 py-3 text-left font-medium">Nº Nota</th>
                        <th class="px-4 py-3 text-right font-medium">Valor</th>
                        <th class="px-4 py-3 text-left font-medium">Arquivo</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Emissão</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="invoice in invoices"
                        :key="invoice.id"
                        class="border-t"
                    >
                        <td class="px-4 py-3">
                            {{ invoice.payrollCycle?.mes_referencia ?? '—' }}
                        </td>
                        <td class="px-4 py-3">{{ invoice.numero_nota }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ formatCurrency(invoice.valor) }}
                        </td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            {{ invoice.arquivo_nome_original }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="statusClass(invoice.status)"
                            >
                                {{ statusLabel(invoice.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ formatDate(invoice.data_emissao) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
