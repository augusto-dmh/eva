<script setup lang="ts">
import type { ProfessionalHistoryEntry } from '@/types/collaborator';

type Props = {
    entries: ProfessionalHistoryEntry[];
};

defineProps<Props>();

const EVENT_TYPE_LABELS: Record<string, string> = {
    admissao: 'Admissão',
    promocao: 'Promoção',
    ajuste_salarial: 'Ajuste Salarial',
    alteracao_tipo_contrato: 'Alteração de Tipo de Contrato',
    desligamento: 'Desligamento',
    dissidio: 'Dissídio',
    alteracao_cargo: 'Alteração de Cargo',
    alteracao_departamento: 'Alteração de Departamento',
};

const MOTIVO_LABELS: Record<string, string> = {
    merito: 'Mérito',
    dissidio: 'Dissídio',
    promocao: 'Promoção',
    reajuste: 'Reajuste',
    correcao: 'Correção',
    politica: 'Política Salarial',
};

function eventTypeLabel(tipo: string): string {
    return EVENT_TYPE_LABELS[tipo] ?? tipo;
}

function motivoLabel(motivo: string): string {
    return MOTIVO_LABELS[motivo] ?? motivo;
}

function formatDate(value: string): string {
    const [year, month, day] = value.split('-');

    return `${day}/${month}/${year}`;
}
</script>

<template>
    <div v-if="entries.length === 0" class="py-4 text-sm text-muted-foreground">
        Nenhum registro no histórico profissional.
    </div>

    <div v-else class="relative flex flex-col gap-0">
        <div
            v-for="(entry, idx) in entries"
            :key="entry.id"
            class="relative flex gap-4 pb-6"
        >
            <!-- Timeline line -->
            <div class="flex flex-col items-center">
                <div
                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-primary bg-background"
                >
                    <div class="h-2 w-2 rounded-full bg-primary" />
                </div>
                <div
                    v-if="idx < entries.length - 1"
                    class="w-0.5 flex-1 bg-border"
                />
            </div>

            <!-- Entry content -->
            <div class="flex-1 pb-2">
                <div class="flex items-center gap-2">
                    <span class="font-medium">
                        {{ eventTypeLabel(entry.tipo_evento) }}
                    </span>
                    <span class="text-xs text-muted-foreground">
                        {{ formatDate(entry.data_efetivacao) }}
                    </span>
                </div>

                <div class="mt-1 text-sm text-muted-foreground">
                    <span>{{ entry.campo_alterado }}</span>
                    <span v-if="entry.valor_anterior || entry.valor_novo">
                        :
                        <span v-if="entry.valor_anterior">
                            {{ entry.valor_anterior }}
                        </span>
                        <span v-if="entry.valor_anterior && entry.valor_novo">
                            →
                        </span>
                        <span
                            v-if="entry.valor_novo"
                            class="font-medium text-foreground"
                        >
                            {{ entry.valor_novo }}
                        </span>
                    </span>
                </div>

                <div class="mt-0.5 text-xs text-muted-foreground">
                    Motivo: {{ motivoLabel(entry.motivo) }}
                </div>

                <div
                    v-if="entry.observacoes"
                    class="mt-0.5 text-xs text-muted-foreground"
                >
                    {{ entry.observacoes }}
                </div>
            </div>
        </div>
    </div>
</template>
