<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    VacationBatch,
    VacationBatchStatus,
    VacationBatchType,
} from '@/types/vacation';

type Props = {
    batches: {
        data: VacationBatch[];
        links: { url: string | null; label: string; active: boolean }[];
    };
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Férias', href: '/vacation-batches' }],
    },
});

const showForm = ref(false);
const mesReferencia = ref('');
const tipo = ref<VacationBatchType>('clt');

function createBatch() {
    router.post(
        '/vacation-batches',
        { mes_referencia: mesReferencia.value, tipo: tipo.value },
        {
            onSuccess: () => {
                showForm.value = false;
                mesReferencia.value = '';
                tipo.value = 'clt';
            },
        },
    );
}

function tipoLabel(t: VacationBatchType): string {
    switch (t) {
        case 'clt':
            return 'CLT';
        case 'estagiario':
            return 'Estagiário';
    }
}

function tipoClass(t: VacationBatchType): string {
    switch (t) {
        case 'clt':
            return 'bg-blue-100 text-blue-800';
        case 'estagiario':
            return 'bg-purple-100 text-purple-800';
    }
}

function statusLabel(status: VacationBatchStatus): string {
    switch (status) {
        case 'rascunho':
            return 'Rascunho';
        case 'calculado':
            return 'Calculado';
        case 'em_revisao':
            return 'Em Revisão';
        case 'confirmado':
            return 'Confirmado';
        case 'concluido':
            return 'Concluído';
    }
}

function statusClass(status: VacationBatchStatus): string {
    switch (status) {
        case 'rascunho':
            return 'bg-gray-100 text-gray-800';
        case 'calculado':
            return 'bg-blue-100 text-blue-800';
        case 'em_revisao':
            return 'bg-blue-100 text-blue-800';
        case 'confirmado':
            return 'bg-teal-100 text-teal-800';
        case 'concluido':
            return 'bg-green-100 text-green-800';
    }
}
</script>

<template>
    <Head title="Férias" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Lotes de Férias</h1>
            <Button @click="showForm = !showForm">
                {{ showForm ? 'Cancelar' : 'Novo Lote' }}
            </Button>
        </div>

        <!-- New batch form -->
        <div v-if="showForm" class="rounded-lg border p-4">
            <form class="flex items-end gap-3" @submit.prevent="createBatch">
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
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Tipo</label>
                    <select
                        v-model="tipo"
                        class="rounded-md border px-3 py-2 text-sm"
                    >
                        <option value="clt">CLT</option>
                        <option value="estagiario">Estagiário</option>
                    </select>
                </div>
                <Button type="submit">Criar Lote</Button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">
                            Mês Referência
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Tipo</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Colaboradores
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="batch in batches.data"
                        :key="batch.id"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ batch.mes_referencia }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="tipoClass(batch.tipo)"
                            >
                                {{ tipoLabel(batch.tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusClass(batch.status)"
                            >
                                {{ statusLabel(batch.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ batch.collaborators_count ?? 0 }}
                        </td>
                        <td class="px-4 py-3">
                            <Button variant="outline" size="sm" as-child>
                                <a :href="`/vacation-batches/${batch.id}`"
                                    >Ver</a
                                >
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="batches.data.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhum lote encontrado.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="batches.links.length > 3"
            class="flex items-center justify-center gap-1"
        >
            <template v-for="link in batches.links" :key="link.label">
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
