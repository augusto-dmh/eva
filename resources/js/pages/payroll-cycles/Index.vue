<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { PayrollCycle, PayrollCycleStatus } from '@/types/payroll';

type Props = {
    cycles: {
        data: PayrollCycle[];
        links: { url: string | null; label: string; active: boolean }[];
    };
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Folha de Pagamento', href: '/payroll-cycles' }],
    },
});

const showForm = ref(false);
const mesReferencia = ref('');

function openCycle() {
    router.post(
        '/payroll-cycles',
        { mes_referencia: mesReferencia.value },
        {
            onSuccess: () => {
                showForm.value = false;
                mesReferencia.value = '';
            },
        },
    );
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
</script>

<template>
    <Head title="Folha de Pagamento" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Folha de Pagamento</h1>
            <Button @click="showForm = !showForm">
                {{ showForm ? 'Cancelar' : 'Abrir Novo Ciclo' }}
            </Button>
        </div>

        <!-- New cycle form -->
        <div v-if="showForm" class="rounded-lg border p-4">
            <form class="flex items-end gap-3" @submit.prevent="openCycle">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium"
                        >Mês de Referência (YYYY-MM)</label
                    >
                    <Input
                        v-model="mesReferencia"
                        placeholder="2026-04"
                        pattern="^\d{4}-(0[1-9]|1[0-2])$"
                        required
                        class="w-40"
                    />
                </div>
                <Button type="submit">Abrir Ciclo</Button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Mês/Ano</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Bruto</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Comissões
                        </th>
                        <th class="px-4 py-3 text-right font-medium">
                            Líquido
                        </th>
                        <th class="px-4 py-3 text-right font-medium">PJ</th>
                        <th class="px-4 py-3 text-left font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="cycle in cycles.data"
                        :key="cycle.id"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ cycle.mes_referencia }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusClass(cycle.status)"
                            >
                                {{ statusLabel(cycle.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ formatCurrency(cycle.salarios_brutos) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ formatCurrency(cycle.comissoes) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ formatCurrency(cycle.liquido) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ formatCurrency(cycle.pj) }}
                        </td>
                        <td class="px-4 py-3">
                            <Button variant="outline" size="sm" as-child>
                                <a :href="`/payroll-cycles/${cycle.id}`">Ver</a>
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="cycles.data.length === 0">
                        <td
                            colspan="7"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhum ciclo encontrado.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="cycles.links.length > 3"
            class="flex items-center justify-center gap-1"
        >
            <template v-for="link in cycles.links" :key="link.label">
                <Button
                    v-if="link.url"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    as-child
                >
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <a :href="link.url"><span v-html="link.label" /></a>
                </Button>
                <Button v-else variant="outline" size="sm" disabled>
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <span v-html="link.label" />
                </Button>
            </template>
        </div>
    </div>
</template>
