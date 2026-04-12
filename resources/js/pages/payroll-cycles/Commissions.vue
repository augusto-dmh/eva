<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { PayrollCycle, PayrollEntry } from '@/types/payroll';

type Props = {
    cycle: PayrollCycle;
    entries: PayrollEntry[];
};

const props = defineProps<Props>();

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

function commissionBadgeVariant(
    tipo: string,
): 'default' | 'secondary' | 'outline' {
    if (tipo === 'closer') {
        return 'default';
    }

    if (tipo === 'advisor') {
        return 'secondary';
    }

    return 'outline';
}

// Per-entry inline forms
const forms = Object.fromEntries(
    props.entries.map((e) => [
        e.id,
        useForm({
            valor_comissao_bruta: e.valor_comissao_bruta ?? '',
            observacoes: e.observacoes ?? '',
        }),
    ]),
);

const saving = ref<Record<number, boolean>>({});

function save(entry: PayrollEntry) {
    const form = forms[entry.id];

    if (!form) {
        return;
    }

    saving.value[entry.id] = true;
    form.put(`/payroll-entries/${entry.id}`, {
        preserveScroll: true,
        onFinish: () => {
            saving.value[entry.id] = false;
        },
    });
}
</script>

<template>
    <Head :title="`Comissões — ${cycle.mes_referencia}`" />

    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Comissões</h1>
                <p class="text-muted-foreground">
                    Ciclo {{ cycle.mes_referencia }}
                </p>
            </div>
            <Link :href="`/payroll-cycles/${cycle.id}`">
                <Button variant="outline">← Voltar ao ciclo</Button>
            </Link>
        </div>

        <!-- Empty state -->
        <div
            v-if="entries.length === 0"
            class="rounded-lg border border-dashed p-12 text-center text-muted-foreground"
        >
            Nenhum colaborador com comissão neste ciclo
        </div>

        <!-- Entries table -->
        <div v-else class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">
                            Colaborador
                        </th>
                        <th class="px-4 py-3 text-left font-medium">
                            Tipo Comissão
                        </th>
                        <th class="px-4 py-3 text-right font-medium">
                            Comissão Bruta
                        </th>
                        <th class="px-4 py-3 text-right font-medium">DSR</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Total Comissão
                        </th>
                        <th class="px-4 py-3 text-center font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="entry in entries" :key="entry.id">
                        <tr
                            class="border-b transition-colors hover:bg-muted/30"
                        >
                            <td class="px-4 py-3 font-medium">
                                {{ entry.collaborator?.nome_completo ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        commissionBadgeVariant(
                                            entry.collaborator?.tipo_comissao ??
                                                '',
                                        )
                                    "
                                >
                                    {{
                                        entry.collaborator?.tipo_comissao ?? '—'
                                    }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Input
                                    v-model="
                                        forms[entry.id].valor_comissao_bruta
                                    "
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-32 text-right"
                                />
                            </td>
                            <td
                                class="px-4 py-3 text-right text-muted-foreground"
                            >
                                {{ formatCurrency(entry.valor_dsr) }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ formatCurrency(entry.valor_comissao_total) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <Button
                                    size="sm"
                                    :disabled="
                                        forms[entry.id].processing ||
                                        saving[entry.id]
                                    "
                                    @click="save(entry)"
                                >
                                    {{
                                        forms[entry.id].processing
                                            ? 'Salvando…'
                                            : 'Salvar'
                                    }}
                                </Button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
