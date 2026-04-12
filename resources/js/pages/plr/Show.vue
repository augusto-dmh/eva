<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PlrRound } from '@/types/plr';

type Props = {
    round: PlrRound;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'PLR', href: '/plr' },
            { title: 'Detalhes', href: '#' },
        ],
    },
});

const simulateForm = useForm({
    valor_total: '',
});

function statusLabel(status: string): string {
    switch (status) {
        case 'rascunho':
            return 'Rascunho';
        case 'documento_enviado':
            return 'Documento Enviado';
        case 'comite_criado':
            return 'Comitê Criado';
        case 'aguardando_sindicato':
            return 'Aguardando Sindicato';
        case 'aprovado':
            return 'Aprovado';
        case 'simulado':
            return 'Simulado';
        case 'pago':
            return 'Pago';
        default:
            return status;
    }
}

function statusVariant(status: string) {
    switch (status) {
        case 'pago':
            return 'default' as const;
        case 'rascunho':
            return 'secondary' as const;
        default:
            return 'outline' as const;
    }
}

function formatCurrency(value: number | string | null): string {
    if (value === null || value === undefined) {
        return '—';
    }

    const num = parseFloat(String(value));

    if (isNaN(num)) {
        return String(value);
    }

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(num);
}

function submitSimulate() {
    simulateForm.post(`/plr/${props.round.id}/simulate`);
}
</script>

<template>
    <Head title="PLR" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold">
                    PLR {{ round.ano_referencia }}
                </h1>
                <Badge :variant="statusVariant(round.status)">
                    {{ statusLabel(round.status) }}
                </Badge>
            </div>
            <Button variant="outline" as-child>
                <Link href="/plr">Voltar</Link>
            </Button>
        </div>

        <!-- Round Details -->
        <Card>
            <CardHeader>
                <CardTitle>Detalhes da Rodada</CardTitle>
            </CardHeader>
            <CardContent
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p class="text-sm text-muted-foreground">
                        Ano de Referência
                    </p>
                    <p class="font-medium">{{ round.ano_referencia }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Total Distribuído
                    </p>
                    <p class="font-medium">
                        {{ formatCurrency(round.valor_total_distribuido) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Criado por</p>
                    <p class="font-medium">
                        {{ round.criado_por?.name ?? '—' }}
                    </p>
                </div>
                <div
                    v-if="round.observacoes"
                    class="md:col-span-2 lg:col-span-3"
                >
                    <p class="text-sm text-muted-foreground">Observações</p>
                    <p class="font-medium">{{ round.observacoes }}</p>
                </div>
            </CardContent>
        </Card>

        <!-- Simulate -->
        <Card v-if="round.status !== 'pago'">
            <CardHeader>
                <CardTitle>Simular PLR</CardTitle>
            </CardHeader>
            <CardContent>
                <form
                    class="flex items-end gap-4"
                    @submit.prevent="submitSimulate"
                >
                    <div class="flex flex-col gap-1">
                        <Label for="valor_total">
                            Valor Total a Distribuir (R$)
                        </Label>
                        <Input
                            id="valor_total"
                            v-model="simulateForm.valor_total"
                            type="number"
                            step="0.01"
                            min="0.01"
                            placeholder="100000.00"
                            required
                        />
                        <p
                            v-if="simulateForm.errors.valor_total"
                            class="text-sm text-destructive"
                        >
                            {{ simulateForm.errors.valor_total }}
                        </p>
                    </div>
                    <Button type="submit" :disabled="simulateForm.processing">
                        Simular PLR
                    </Button>
                </form>
            </CardContent>
        </Card>

        <!-- Entries Table -->
        <Card v-if="round.entries && round.entries.length > 0">
            <CardHeader>
                <CardTitle>
                    Colaboradores ({{ round.entries.length }})
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">
                                    Colaborador
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Meses
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Valor Simulado
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    IRRF
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="entry in round.entries"
                                :key="entry.id"
                                class="hover:bg-muted/30"
                            >
                                <td class="px-4 py-3">
                                    {{
                                        entry.collaborator?.nome_completo ?? '—'
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ entry.meses_trabalhados }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{ formatCurrency(entry.valor_simulado) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-destructive"
                                >
                                    {{ formatCurrency(entry.desconto_irrf) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ entry.status }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Committee Members -->
        <Card
            v-if="round.committee_members && round.committee_members.length > 0"
        >
            <CardHeader>
                <CardTitle>
                    Comitê ({{ round.committee_members.length }})
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">
                                    Colaborador
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Empresa
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Papel
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Ativo
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="member in round.committee_members"
                                :key="member.id"
                                class="hover:bg-muted/30"
                            >
                                <td class="px-4 py-3">
                                    {{
                                        member.collaborator?.nome_completo ??
                                        '—'
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ member.legal_entity?.apelido ?? '—' }}
                                </td>
                                <td class="px-4 py-3 capitalize">
                                    {{ member.papel }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ member.ativo ? 'Sim' : 'Não' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
