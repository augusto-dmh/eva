<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { update as updateItem } from '@/routes/admission-checklist-items';
import type {
    AdmissionChecklist,
    AdmissionChecklistItem,
    ChecklistStatus,
} from '@/types/checklist';

type Props = {
    checklist: AdmissionChecklist & { items: AdmissionChecklistItem[] };
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Colaboradores', href: '/collaborators' },
            { title: 'Checklist de Admissão', href: '#' },
        ],
    },
});

function statusVariant(status: ChecklistStatus) {
    switch (status) {
        case 'pendente':
            return 'secondary';
        case 'em_andamento':
            return 'default';
        case 'completo':
            return 'default';
        case 'bloqueado':
            return 'destructive';
    }
}

function statusLabel(status: ChecklistStatus) {
    switch (status) {
        case 'pendente':
            return 'Pendente';
        case 'em_andamento':
            return 'Em Andamento';
        case 'completo':
            return 'Completo';
        case 'bloqueado':
            return 'Bloqueado';
    }
}

function contractLabel(tipo: string) {
    switch (tipo) {
        case 'clt':
            return 'CLT';
        case 'pj':
            return 'PJ';
        case 'estagiario':
            return 'Estagiário';
        case 'socio':
            return 'Sócio';
        default:
            return tipo;
    }
}

function formatDate(value: string | null) {
    if (!value) {
        return '—';
    }

    const [year, month, day] = value.split('-');

    return `${day}/${month}/${year}`;
}

function formatDateTime(value: string | null) {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    return (
        date.toLocaleDateString('pt-BR') +
        ' ' +
        date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
    );
}

const mandatoryItems = () =>
    (props.checklist.items ?? []).filter((i) => i.obrigatorio);
const confirmedMandatory = () => mandatoryItems().filter((i) => i.confirmado);

function confirmItem(item: AdmissionChecklistItem) {
    if (item.confirmado) {
        return;
    }

    router.put(
        updateItem(item).url,
        {},
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Head title="Checklist de Admissão" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold">Checklist de Admissão</h1>
                <Badge :variant="statusVariant(checklist.status)">
                    {{ statusLabel(checklist.status) }}
                </Badge>
                <Badge variant="outline">
                    {{ contractLabel(checklist.tipo_contrato) }}
                </Badge>
            </div>
            <Button variant="outline" as-child>
                <Link href="/collaborators">Voltar</Link>
            </Button>
        </div>

        <!-- Info card -->
        <Card>
            <CardHeader>
                <CardTitle>Informações</CardTitle>
            </CardHeader>
            <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div v-if="checklist.collaborator">
                    <p class="text-sm text-muted-foreground">Colaborador</p>
                    <p class="font-medium">
                        {{ checklist.collaborator.nome_completo }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Data Limite</p>
                    <p class="font-medium">
                        {{ formatDate(checklist.data_limite) }}
                    </p>
                </div>
                <div v-if="checklist.completado_em">
                    <p class="text-sm text-muted-foreground">Concluído em</p>
                    <p class="font-medium">
                        {{ formatDateTime(checklist.completado_em) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Progresso (obrigatórios)
                    </p>
                    <p class="font-medium">
                        {{ confirmedMandatory().length }} de
                        {{ mandatoryItems().length }} itens obrigatórios
                        confirmados
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Items list -->
        <Card>
            <CardHeader>
                <CardTitle>Itens do Checklist</CardTitle>
            </CardHeader>
            <CardContent>
                <!-- Empty state -->
                <div
                    v-if="!checklist.items || checklist.items.length === 0"
                    class="py-8 text-center text-muted-foreground"
                >
                    Nenhum item encontrado neste checklist.
                </div>

                <ul v-else class="divide-y">
                    <li
                        v-for="item in checklist.items"
                        :key="item.id"
                        class="flex items-start gap-3 py-3"
                    >
                        <button
                            type="button"
                            :disabled="item.confirmado"
                            :aria-label="
                                item.confirmado
                                    ? 'Item confirmado'
                                    : 'Confirmar item'
                            "
                            class="mt-0.5 shrink-0 focus:outline-none"
                            @click="confirmItem(item)"
                        >
                            <span
                                :class="[
                                    'flex h-5 w-5 items-center justify-center rounded border-2',
                                    item.confirmado
                                        ? 'border-green-500 bg-green-500 text-white'
                                        : 'border-gray-300 hover:border-primary',
                                ]"
                            >
                                <svg
                                    v-if="item.confirmado"
                                    class="h-3 w-3"
                                    viewBox="0 0 12 12"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M2 6l3 3 5-5" />
                                </svg>
                            </span>
                        </button>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    :class="[
                                        'text-sm font-medium',
                                        item.confirmado
                                            ? 'text-muted-foreground line-through'
                                            : '',
                                    ]"
                                >
                                    {{ item.descricao }}
                                </span>
                                <Badge
                                    v-if="item.obrigatorio"
                                    variant="secondary"
                                    class="text-xs"
                                >
                                    Obrigatório
                                </Badge>
                            </div>
                            <p
                                v-if="item.confirmado && item.confirmado_em"
                                class="mt-0.5 text-xs text-muted-foreground"
                            >
                                Confirmado em
                                {{ formatDateTime(item.confirmado_em) }}
                            </p>
                        </div>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
