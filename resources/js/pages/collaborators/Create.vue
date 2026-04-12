<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { create, index, store } from '@/routes/collaborators';
import type { EnumOption, LegalEntity } from '@/types/collaborator';

type Props = {
    legalEntities: LegalEntity[];
    contractTypes: EnumOption[];
    commissionTypes: EnumOption[];
    statuses: EnumOption[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Colaboradores', href: index() },
            { title: 'Novo Colaborador', href: create() },
        ],
    },
});

const form = useForm({
    nome_completo: '',
    cpf: '',
    email_corporativo: '',
    email_pessoal: '',
    data_nascimento: '',
    telefone: '',
    tipo_contrato: '',
    legal_entity_id: '',
    departamento: '',
    cargo: '',
    nivel: '',
    trilha_carreira: '',
    lider_direto: '',
    status: 'ativo',
    data_admissao: '',
    data_desligamento: '',
    flash_numero_cartao: '',
    flash_vale_alimentacao: '',
    flash_vale_refeicao: '',
    flash_vale_transporte: '',
    flash_saude: '',
    flash_cultura: '',
    flash_educacao: '',
    flash_home_office: '',
    salario_base: '',
    tipo_comissao: 'none',
    minimo_garantido: '',
    elegivel_comissao: false,
    desconto_petlove: '',
    banco: '',
    agencia: '',
    conta: '',
    chave_pix: '',
    pis: '',
    slack_user_id: '',
});

function submit() {
    form.post(store().url);
}
</script>

<template>
    <Head title="Novo Colaborador" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Novo Colaborador</h1>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <!-- Dados Pessoais -->
            <Card>
                <CardHeader>
                    <CardTitle>Dados Pessoais</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="nome_completo">Nome Completo *</Label>
                        <Input
                            id="nome_completo"
                            v-model="form.nome_completo"
                            required
                        />
                        <InputError :message="form.errors.nome_completo" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="cpf">CPF *</Label>
                        <Input id="cpf" v-model="form.cpf" required />
                        <InputError :message="form.errors.cpf" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email_corporativo"
                            >E-mail Corporativo *</Label
                        >
                        <Input
                            id="email_corporativo"
                            type="email"
                            v-model="form.email_corporativo"
                            required
                        />
                        <InputError :message="form.errors.email_corporativo" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email_pessoal">E-mail Pessoal</Label>
                        <Input
                            id="email_pessoal"
                            type="email"
                            v-model="form.email_pessoal"
                        />
                        <InputError :message="form.errors.email_pessoal" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="data_nascimento">Data de Nascimento</Label>
                        <Input
                            id="data_nascimento"
                            type="date"
                            v-model="form.data_nascimento"
                        />
                        <InputError :message="form.errors.data_nascimento" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="telefone">Telefone</Label>
                        <Input id="telefone" v-model="form.telefone" />
                        <InputError :message="form.errors.telefone" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="pis">PIS</Label>
                        <Input id="pis" v-model="form.pis" />
                        <InputError :message="form.errors.pis" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="slack_user_id">Slack User ID</Label>
                        <Input
                            id="slack_user_id"
                            v-model="form.slack_user_id"
                        />
                        <InputError :message="form.errors.slack_user_id" />
                    </div>
                </CardContent>
            </Card>

            <!-- Vínculo Empregatício -->
            <Card>
                <CardHeader>
                    <CardTitle>Vínculo Empregatício</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="tipo_contrato">Tipo de Contrato *</Label>
                        <Select v-model="form.tipo_contrato">
                            <SelectTrigger id="tipo_contrato">
                                <SelectValue placeholder="Selecione..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="ct in contractTypes"
                                    :key="ct.value"
                                    :value="ct.value"
                                >
                                    {{ ct.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.tipo_contrato" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="legal_entity_id">Empresa *</Label>
                        <Select v-model="form.legal_entity_id">
                            <SelectTrigger id="legal_entity_id">
                                <SelectValue placeholder="Selecione..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="le in legalEntities"
                                    :key="le.id"
                                    :value="String(le.id)"
                                >
                                    {{ le.apelido }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.legal_entity_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="status">Status *</Label>
                        <Select v-model="form.status">
                            <SelectTrigger id="status">
                                <SelectValue placeholder="Selecione..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="st in statuses"
                                    :key="st.value"
                                    :value="st.value"
                                >
                                    {{ st.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.status" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="data_admissao">Data de Admissão *</Label>
                        <Input
                            id="data_admissao"
                            type="date"
                            v-model="form.data_admissao"
                            required
                        />
                        <InputError :message="form.errors.data_admissao" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="data_desligamento"
                            >Data de Desligamento</Label
                        >
                        <Input
                            id="data_desligamento"
                            type="date"
                            v-model="form.data_desligamento"
                        />
                        <InputError :message="form.errors.data_desligamento" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="departamento">Departamento</Label>
                        <Input id="departamento" v-model="form.departamento" />
                        <InputError :message="form.errors.departamento" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="cargo">Cargo</Label>
                        <Input id="cargo" v-model="form.cargo" />
                        <InputError :message="form.errors.cargo" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="nivel">Nível</Label>
                        <Input id="nivel" v-model="form.nivel" />
                        <InputError :message="form.errors.nivel" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="trilha_carreira">Trilha de Carreira</Label>
                        <Input
                            id="trilha_carreira"
                            v-model="form.trilha_carreira"
                        />
                        <InputError :message="form.errors.trilha_carreira" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="lider_direto">Líder Direto</Label>
                        <Input id="lider_direto" v-model="form.lider_direto" />
                        <InputError :message="form.errors.lider_direto" />
                    </div>
                </CardContent>
            </Card>

            <!-- Benefícios Flash -->
            <Card>
                <CardHeader>
                    <CardTitle>Benefícios Flash</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="flash_numero_cartao"
                            >Número do Cartão</Label
                        >
                        <Input
                            id="flash_numero_cartao"
                            v-model="form.flash_numero_cartao"
                        />
                        <InputError
                            :message="form.errors.flash_numero_cartao"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="flash_vale_alimentacao"
                            >Vale Alimentação</Label
                        >
                        <Input
                            id="flash_vale_alimentacao"
                            v-model="form.flash_vale_alimentacao"
                        />
                        <InputError
                            :message="form.errors.flash_vale_alimentacao"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="flash_vale_refeicao">Vale Refeição</Label>
                        <Input
                            id="flash_vale_refeicao"
                            v-model="form.flash_vale_refeicao"
                        />
                        <InputError
                            :message="form.errors.flash_vale_refeicao"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="flash_vale_transporte"
                            >Vale Transporte</Label
                        >
                        <Input
                            id="flash_vale_transporte"
                            v-model="form.flash_vale_transporte"
                        />
                        <InputError
                            :message="form.errors.flash_vale_transporte"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="flash_saude">Saúde</Label>
                        <Input id="flash_saude" v-model="form.flash_saude" />
                        <InputError :message="form.errors.flash_saude" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="flash_cultura">Cultura</Label>
                        <Input
                            id="flash_cultura"
                            v-model="form.flash_cultura"
                        />
                        <InputError :message="form.errors.flash_cultura" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="flash_educacao">Educação</Label>
                        <Input
                            id="flash_educacao"
                            v-model="form.flash_educacao"
                        />
                        <InputError :message="form.errors.flash_educacao" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="flash_home_office">Home Office</Label>
                        <Input
                            id="flash_home_office"
                            v-model="form.flash_home_office"
                        />
                        <InputError :message="form.errors.flash_home_office" />
                    </div>
                </CardContent>
            </Card>

            <!-- Remuneração -->
            <Card>
                <CardHeader>
                    <CardTitle>Remuneração</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="salario_base">Salário Base *</Label>
                        <Input
                            id="salario_base"
                            v-model="form.salario_base"
                            required
                        />
                        <InputError :message="form.errors.salario_base" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="tipo_comissao">Tipo de Comissão</Label>
                        <Select v-model="form.tipo_comissao">
                            <SelectTrigger id="tipo_comissao">
                                <SelectValue placeholder="Selecione..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="ct in commissionTypes"
                                    :key="ct.value"
                                    :value="ct.value"
                                >
                                    {{ ct.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.tipo_comissao" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="minimo_garantido">Mínimo Garantido</Label>
                        <Input
                            id="minimo_garantido"
                            v-model="form.minimo_garantido"
                        />
                        <InputError :message="form.errors.minimo_garantido" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="desconto_petlove">Desconto Petlove</Label>
                        <Input
                            id="desconto_petlove"
                            v-model="form.desconto_petlove"
                        />
                        <InputError :message="form.errors.desconto_petlove" />
                    </div>
                    <div class="flex items-center gap-2 md:col-span-2">
                        <Checkbox
                            id="elegivel_comissao"
                            v-model:checked="form.elegivel_comissao"
                        />
                        <Label for="elegivel_comissao"
                            >Elegível para Comissão</Label
                        >
                        <InputError :message="form.errors.elegivel_comissao" />
                    </div>
                </CardContent>
            </Card>

            <!-- Dados Bancários -->
            <Card>
                <CardHeader>
                    <CardTitle>Dados Bancários</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="banco">Banco</Label>
                        <Input id="banco" v-model="form.banco" />
                        <InputError :message="form.errors.banco" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="agencia">Agência</Label>
                        <Input id="agencia" v-model="form.agencia" />
                        <InputError :message="form.errors.agencia" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="conta">Conta</Label>
                        <Input id="conta" v-model="form.conta" />
                        <InputError :message="form.errors.conta" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="chave_pix">Chave PIX</Label>
                        <Input id="chave_pix" v-model="form.chave_pix" />
                        <InputError :message="form.errors.chave_pix" />
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="form.processing"
                    >Salvar</Button
                >
                <Button variant="outline" as-child>
                    <Link :href="index()">Cancelar</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
