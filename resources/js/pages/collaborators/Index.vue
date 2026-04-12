<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index, create, show, edit } from '@/routes/collaborators';
import type {
    Collaborator,
    CollaboratorStatus,
    ContractType,
    EnumOption,
    LegalEntity,
} from '@/types/collaborator';

type Props = {
    collaborators: {
        data: Collaborator[];
        links: { url: string | null; label: string; active: boolean }[];
        meta?: Record<string, unknown>;
    };
    filters: {
        search?: string;
        tipo_contrato?: string;
        legal_entity_id?: string;
        status?: string;
    };
    legalEntities: LegalEntity[];
    contractTypes: EnumOption[];
    statuses: EnumOption[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Colaboradores',
                href: index(),
            },
        ],
    },
});

const search = ref(props.filters.search ?? '');
const filterContrato = ref(props.filters.tipo_contrato ?? '');
const filterEmpresa = ref(props.filters.legal_entity_id ?? '');
const filterStatus = ref(props.filters.status ?? '');

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

function applyFilters() {
    router.get(
        index(),
        {
            search: search.value || undefined,
            tipo_contrato: filterContrato.value || undefined,
            legal_entity_id: filterEmpresa.value || undefined,
            status: filterStatus.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

watch(search, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(applyFilters, 300);
});

watch([filterContrato, filterEmpresa, filterStatus], applyFilters);

function statusVariant(status: CollaboratorStatus) {
    switch (status) {
        case 'ativo':
            return 'default';
        case 'desligado':
            return 'destructive';
        case 'afastado':
            return 'secondary';
    }
}

function statusLabel(status: CollaboratorStatus) {
    switch (status) {
        case 'ativo':
            return 'Ativo';
        case 'desligado':
            return 'Desligado';
        case 'afastado':
            return 'Afastado';
    }
}

function contractVariant(tipo: ContractType) {
    switch (tipo) {
        case 'clt':
            return 'default';
        case 'pj':
            return 'secondary';
        case 'estagiario':
            return 'outline';
        case 'socio':
            return 'secondary';
    }
}

function contractLabel(tipo: ContractType) {
    switch (tipo) {
        case 'clt':
            return 'CLT';
        case 'pj':
            return 'PJ';
        case 'estagiario':
            return 'Estagiário';
        case 'socio':
            return 'Sócio';
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
    <Head title="Colaboradores" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Colaboradores</h1>
            <Button as-child>
                <Link :href="create()">Novo Colaborador</Link>
            </Button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <Input
                v-model="search"
                placeholder="Buscar por nome, CPF ou e-mail..."
                class="max-w-xs"
            />

            <Select v-model="filterContrato">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Contrato" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="">Todos</SelectItem>
                    <SelectItem
                        v-for="ct in contractTypes"
                        :key="ct.value"
                        :value="ct.value"
                    >
                        {{ ct.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="filterEmpresa">
                <SelectTrigger class="w-48">
                    <SelectValue placeholder="Empresa" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="">Todas</SelectItem>
                    <SelectItem
                        v-for="le in legalEntities"
                        :key="le.id"
                        :value="String(le.id)"
                    >
                        {{ le.apelido }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="filterStatus">
                <SelectTrigger class="w-40">
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="">Todos</SelectItem>
                    <SelectItem
                        v-for="st in statuses"
                        :key="st.value"
                        :value="st.value"
                    >
                        {{ st.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Nome</th>
                        <th class="px-4 py-3 text-left font-medium">CPF</th>
                        <th class="px-4 py-3 text-left font-medium">
                            Contrato
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Empresa</th>
                        <th class="px-4 py-3 text-left font-medium">
                            Departamento
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Salário
                        </th>
                        <th class="px-4 py-3 text-left font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="collaborator in collaborators.data"
                        :key="collaborator.id"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ collaborator.nome_completo }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ collaborator.cpf }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="
                                    contractVariant(collaborator.tipo_contrato)
                                "
                            >
                                {{ contractLabel(collaborator.tipo_contrato) }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            {{ collaborator.legal_entity?.apelido ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ collaborator.departamento ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                :variant="statusVariant(collaborator.status)"
                            >
                                {{ statusLabel(collaborator.status) }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ formatCurrency(collaborator.salario_base) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="show(collaborator)">Ver</Link>
                                </Button>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="edit(collaborator)"
                                        >Editar</Link
                                    >
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="collaborators.data.length === 0">
                        <td
                            colspan="8"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Nenhum colaborador encontrado.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="collaborators.links.length > 3"
            class="flex items-center justify-center gap-1"
        >
            <template v-for="link in collaborators.links" :key="link.label">
                <Button
                    v-if="link.url"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    as-child
                >
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <Link :href="link.url"><span v-html="link.label" /></Link>
                </Button>
                <Button v-else variant="outline" size="sm" disabled
                    ><span v-html="link.label"
                /></Button>
            </template>
        </div>
    </div>
</template>
