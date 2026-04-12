<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
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
import type { PayrollEntry, PayrollEntryStatus } from '@/types/payroll';

type Props = {
    entry: PayrollEntry;
    cycleId: number;
};

const props = defineProps<Props>();

const editing = ref(false);

const form = ref({
    salario_bruto: props.entry.salario_bruto,
    valor_comissao_bruta: props.entry.valor_comissao_bruta,
    valor_dsr: props.entry.valor_dsr,
    valor_comissao_total: props.entry.valor_comissao_total,
    desconto_inss: props.entry.desconto_inss,
    desconto_irrf: props.entry.desconto_irrf,
    desconto_petlove: props.entry.desconto_petlove,
    desconto_outros: props.entry.desconto_outros,
    bonificacoes: props.entry.bonificacoes,
    valor_liquido: props.entry.valor_liquido,
    observacoes: props.entry.observacoes ?? '',
    status: props.entry.status,
});

function submitEdit() {
    router.put(
        `/payroll-cycles/${props.cycleId}/entries/${props.entry.id}`,
        form.value,
        {
            preserveScroll: true,
            onSuccess: () => {
                editing.value = false;
            },
        },
    );
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

function contractVariant(tipo: string) {
    switch (tipo) {
        case 'clt':
            return 'default';
        case 'pj':
            return 'secondary';
        case 'estagiario':
            return 'outline';
        default:
            return 'secondary';
    }
}

function statusVariant(status: PayrollEntryStatus) {
    switch (status) {
        case 'aprovado':
            return 'default';
        case 'revisado':
            return 'secondary';
        case 'preenchido':
            return 'outline';
        default:
            return 'secondary';
    }
}
</script>

<template>
    <tr class="border-b transition-colors hover:bg-muted/30">
        <td class="px-4 py-3 font-medium">
            {{ entry.collaborator?.nome_completo ?? '—' }}
        </td>
        <td class="px-4 py-3">
            <Badge :variant="contractVariant(entry.tipo_contrato)">
                {{ entry.tipo_contrato.toUpperCase() }}
            </Badge>
        </td>
        <td class="px-4 py-3 text-right">
            {{ formatCurrency(entry.salario_bruto) }}
        </td>
        <td class="px-4 py-3 text-right">
            {{ formatCurrency(entry.valor_liquido) }}
        </td>
        <td class="px-4 py-3">
            <Badge :variant="statusVariant(entry.status)">
                {{ entry.status }}
            </Badge>
        </td>
        <td class="px-4 py-3">
            <Button variant="outline" size="sm" @click="editing = !editing">
                {{ editing ? 'Cancelar' : 'Editar' }}
            </Button>
        </td>
    </tr>

    <!-- Inline edit row -->
    <tr v-if="editing" class="bg-muted/10">
        <td colspan="6" class="px-4 py-4">
            <form
                class="grid grid-cols-2 gap-3 md:grid-cols-4"
                @submit.prevent="submitEdit"
            >
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Salário Bruto</label>
                    <Input
                        v-model="form.salario_bruto"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Comissão Bruta</label>
                    <Input
                        v-model="form.valor_comissao_bruta"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">DSR</label>
                    <Input
                        v-model="form.valor_dsr"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Comissão Total</label>
                    <Input
                        v-model="form.valor_comissao_total"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Desc. INSS</label>
                    <Input
                        v-model="form.desconto_inss"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Desc. IRRF</label>
                    <Input
                        v-model="form.desconto_irrf"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Desc. Petlove</label>
                    <Input
                        v-model="form.desconto_petlove"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Desc. Outros</label>
                    <Input
                        v-model="form.desconto_outros"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Bonificações</label>
                    <Input
                        v-model="form.bonificacoes"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Valor Líquido</label>
                    <Input
                        v-model="form.valor_liquido"
                        type="number"
                        step="0.01"
                        min="0"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Status</label>
                    <Select v-model="form.status">
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pendente">Pendente</SelectItem>
                            <SelectItem value="preenchido"
                                >Preenchido</SelectItem
                            >
                            <SelectItem value="revisado">Revisado</SelectItem>
                            <SelectItem value="aprovado">Aprovado</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium">Observações</label>
                    <Input v-model="form.observacoes" type="text" />
                </div>
                <div class="col-span-2 flex items-end gap-2 md:col-span-4">
                    <Button type="submit" size="sm">Salvar</Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="editing = false"
                    >
                        Cancelar
                    </Button>
                </div>
            </form>
        </td>
    </tr>
</template>
