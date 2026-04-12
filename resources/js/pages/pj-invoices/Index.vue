<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { PayrollCycle } from '@/types/payroll';
import type { PjInvoice, PjInvoiceStatus } from '@/types/pj-invoice';

type Props = {
    cycle: PayrollCycle;
    invoices: PjInvoice[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Folha de Pagamento', href: '/payroll-cycles' },
            { title: 'Notas Fiscais PJ', href: '#' },
        ],
    },
});

function statusClass(status: PjInvoiceStatus): string {
    switch (status) {
        case 'pendente':
            return 'bg-blue-100 text-blue-800';
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
</script>

<template>
    <Head :title="`Notas Fiscais PJ — ${cycle.mes_referencia}`" />

    <div class="flex flex-col gap-6 p-6">
        <h1 class="text-2xl font-bold">
            Notas Fiscais PJ — {{ cycle.mes_referencia }}
        </h1>

        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">
                            Colaborador
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Nº Nota</th>
                        <th class="px-4 py-3 text-right font-medium">Valor</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Arquivo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="invoice in invoices"
                        :key="invoice.id"
                        class="border-t"
                    >
                        <td class="px-4 py-3">
                            {{ invoice.collaborator?.nome_completo ?? '—' }}
                        </td>
                        <td class="px-4 py-3">{{ invoice.numero_nota }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ formatCurrency(invoice.valor) }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="statusClass(invoice.status)"
                            >
                                {{ statusLabel(invoice.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            {{ invoice.arquivo_nome_original }}
                        </td>
                    </tr>
                    <tr v-if="invoices.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhuma nota fiscal enviada.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
