<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index, edit } from '@/routes/collaborators';
import type {
    Collaborator,
    CollaboratorStatus,
    ContractType,
} from '@/types/collaborator';

type Props = {
    collaborator: Collaborator;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Colaboradores', href: index() },
            { title: 'Detalhes', href: '#' },
        ],
    },
});

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

function formatDate(value: string | null) {
    if (!value) {
        return '—';
    }

    const [year, month, day] = value.split('-');

    return `${day}/${month}/${year}`;
}
</script>

<template>
    <Head :title="collaborator.nome_completo" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold">
                    {{ collaborator.nome_completo }}
                </h1>
                <Badge :variant="statusVariant(collaborator.status)">
                    {{ statusLabel(collaborator.status) }}
                </Badge>
                <Badge :variant="contractVariant(collaborator.tipo_contrato)">
                    {{ contractLabel(collaborator.tipo_contrato) }}
                </Badge>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" as-child>
                    <Link :href="index()">Voltar</Link>
                </Button>
                <Button as-child>
                    <Link :href="edit(collaborator)">Editar</Link>
                </Button>
            </div>
        </div>

        <!-- Dados Pessoais -->
        <Card>
            <CardHeader>
                <CardTitle>Dados Pessoais</CardTitle>
            </CardHeader>
            <CardContent
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p class="text-sm text-muted-foreground">Nome Completo</p>
                    <p class="font-medium">{{ collaborator.nome_completo }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">CPF</p>
                    <p class="font-medium">{{ collaborator.cpf }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        E-mail Corporativo
                    </p>
                    <p class="font-medium">
                        {{ collaborator.email_corporativo }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">E-mail Pessoal</p>
                    <p class="font-medium">
                        {{ collaborator.email_pessoal ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Data de Nascimento
                    </p>
                    <p class="font-medium">
                        {{ formatDate(collaborator.data_nascimento) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Telefone</p>
                    <p class="font-medium">
                        {{ collaborator.telefone ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">PIS</p>
                    <p class="font-medium">{{ collaborator.pis ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Slack User ID</p>
                    <p class="font-medium">
                        {{ collaborator.slack_user_id ?? '—' }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Vínculo Empregatício -->
        <Card>
            <CardHeader>
                <CardTitle>Vínculo Empregatício</CardTitle>
            </CardHeader>
            <CardContent
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p class="text-sm text-muted-foreground">Empresa</p>
                    <p class="font-medium">
                        {{ collaborator.legal_entity?.nome ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Tipo de Contrato
                    </p>
                    <Badge
                        :variant="contractVariant(collaborator.tipo_contrato)"
                    >
                        {{ contractLabel(collaborator.tipo_contrato) }}
                    </Badge>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Status</p>
                    <Badge :variant="statusVariant(collaborator.status)">
                        {{ statusLabel(collaborator.status) }}
                    </Badge>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Data de Admissão
                    </p>
                    <p class="font-medium">
                        {{ formatDate(collaborator.data_admissao) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Data de Desligamento
                    </p>
                    <p class="font-medium">
                        {{ formatDate(collaborator.data_desligamento) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Departamento</p>
                    <p class="font-medium">
                        {{ collaborator.departamento ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Cargo</p>
                    <p class="font-medium">{{ collaborator.cargo ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Nível</p>
                    <p class="font-medium">{{ collaborator.nivel ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Trilha de Carreira
                    </p>
                    <p class="font-medium">
                        {{ collaborator.trilha_carreira ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Líder Direto</p>
                    <p class="font-medium">
                        {{ collaborator.lider_direto ?? '—' }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Benefícios Flash -->
        <Card>
            <CardHeader>
                <CardTitle>Benefícios Flash</CardTitle>
            </CardHeader>
            <CardContent
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p class="text-sm text-muted-foreground">
                        Número do Cartão
                    </p>
                    <p class="font-medium">
                        {{ collaborator.flash_numero_cartao ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Vale Alimentação
                    </p>
                    <p class="font-medium">
                        {{
                            formatCurrency(collaborator.flash_vale_alimentacao)
                        }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Vale Refeição</p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.flash_vale_refeicao) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Vale Transporte</p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.flash_vale_transporte) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Saúde</p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.flash_saude) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Cultura</p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.flash_cultura) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Educação</p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.flash_educacao) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Home Office</p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.flash_home_office) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Total Flash</p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.flash_total) }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Remuneração -->
        <Card>
            <CardHeader>
                <CardTitle>Remuneração</CardTitle>
            </CardHeader>
            <CardContent
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p class="text-sm text-muted-foreground">Salário Base</p>
                    <p class="text-lg font-medium">
                        {{ formatCurrency(collaborator.salario_base) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Tipo de Comissão
                    </p>
                    <p class="font-medium">{{ collaborator.tipo_comissao }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Mínimo Garantido
                    </p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.minimo_garantido) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Elegível para Comissão
                    </p>
                    <p class="font-medium">
                        {{ collaborator.elegivel_comissao ? 'Sim' : 'Não' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Desconto Petlove
                    </p>
                    <p class="font-medium">
                        {{ formatCurrency(collaborator.desconto_petlove) }}
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Dados Bancários -->
        <Card>
            <CardHeader>
                <CardTitle>Dados Bancários</CardTitle>
            </CardHeader>
            <CardContent
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p class="text-sm text-muted-foreground">Banco</p>
                    <p class="font-medium">{{ collaborator.banco ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Agência</p>
                    <p class="font-medium">{{ collaborator.agencia ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Conta</p>
                    <p class="font-medium">{{ collaborator.conta ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Chave PIX</p>
                    <p class="font-medium">
                        {{ collaborator.chave_pix ?? '—' }}
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
