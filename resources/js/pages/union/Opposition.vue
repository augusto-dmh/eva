<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import type { AssistiveConventionRecord } from '@/types/union';

type Props = {
    records: AssistiveConventionRecord[];
    ano: number;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Sindicato', href: '#' },
            { title: 'Contribuição Assistencial', href: '/union/opposition' },
        ],
    },
});

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    const [year, month, day] = value.split('T')[0].split('-');

    return `${day}/${month}/${year}`;
}

function changeYear(year: number) {
    router.get('/union/opposition', { ano: year }, { preserveState: true });
}
</script>

<template>
    <Head title="Contribuição Assistencial" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Contribuição Assistencial</h1>
            <div class="flex items-center gap-2">
                <label class="text-sm text-muted-foreground">Ano:</label>
                <select
                    :value="ano"
                    class="rounded-lg border px-3 py-1.5 text-sm"
                    @change="
                        changeYear(
                            Number(($event.target as HTMLSelectElement).value),
                        )
                    "
                >
                    <option
                        v-for="y in [2023, 2024, 2025, 2026]"
                        :key="y"
                        :value="y"
                    >
                        {{ y }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">
                            Colaborador
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Tipo</th>
                        <th class="px-4 py-3 text-left font-medium">
                            Fez Oposição
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Data Oposição
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Confirmado Sindicato
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Parcelas Descontadas
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="record in records"
                        :key="record.id"
                        class="hover:bg-muted/30"
                    >
                        <td class="px-4 py-3">
                            {{ record.collaborator?.nome_completo ?? '—' }}
                        </td>
                        <td class="px-4 py-3 capitalize">
                            {{ record.collaborator?.tipo_contrato ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="
                                    record.fez_oposicao
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'
                                "
                            >
                                {{ record.fez_oposicao ? 'Sim' : 'Não' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ formatDate(record.data_oposicao) }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="
                                    record.confirmado_sindicato
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'
                                "
                            >
                                {{
                                    record.confirmado_sindicato ? 'Sim' : 'Não'
                                }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ record.parcelas_descontadas }} /
                            {{ record.total_parcelas }}
                        </td>
                    </tr>
                    <tr v-if="records.length === 0">
                        <td
                            colspan="6"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhum registro encontrado para {{ ano }}.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
