<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: '13º Salário', href: '/thirteenth-salary' },
            { title: 'Novo 13º Salário', href: '#' },
        ],
    },
});

const currentYear = new Date().getFullYear();

const form = useForm({
    ano_referencia: currentYear,
    primeira_parcela_data_limite: `${currentYear}-11-30`,
    segunda_parcela_data_limite: `${currentYear}-12-20`,
    observacoes: '',
});

function submit() {
    form.transform((data) => ({
        ano_referencia: data.ano_referencia,
        primeira_parcela_data_limite: data.primeira_parcela_data_limite,
        segunda_parcela_data_limite: data.segunda_parcela_data_limite,
        observacoes: data.observacoes || null,
    })).post('/thirteenth-salary');
}
</script>

<template>
    <Head title="Novo 13º Salário" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Novo 13º Salário</h1>
            <Button variant="outline" as-child>
                <Link href="/thirteenth-salary">Voltar</Link>
            </Button>
        </div>

        <Card class="max-w-lg">
            <CardHeader>
                <CardTitle>Dados da Rodada</CardTitle>
            </CardHeader>
            <CardContent>
                <form class="flex flex-col gap-4" @submit.prevent="submit">
                    <div class="flex flex-col gap-1">
                        <Label for="ano_referencia">Ano de Referência</Label>
                        <Input
                            id="ano_referencia"
                            v-model.number="form.ano_referencia"
                            type="number"
                            min="2000"
                            max="2100"
                            required
                        />
                        <p
                            v-if="form.errors.ano_referencia"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.ano_referencia }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <Label for="primeira_parcela_data_limite">
                            Data Limite — 1ª Parcela
                        </Label>
                        <Input
                            id="primeira_parcela_data_limite"
                            v-model="form.primeira_parcela_data_limite"
                            type="date"
                            required
                        />
                        <p
                            v-if="form.errors.primeira_parcela_data_limite"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.primeira_parcela_data_limite }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <Label for="segunda_parcela_data_limite">
                            Data Limite — 2ª Parcela
                        </Label>
                        <Input
                            id="segunda_parcela_data_limite"
                            v-model="form.segunda_parcela_data_limite"
                            type="date"
                            required
                        />
                        <p
                            v-if="form.errors.segunda_parcela_data_limite"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.segunda_parcela_data_limite }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <Label for="observacoes">Observações (opcional)</Label>
                        <textarea
                            id="observacoes"
                            v-model="form.observacoes"
                            class="min-h-[80px] rounded-md border px-3 py-2 text-sm"
                            placeholder="Observações sobre esta rodada..."
                        />
                        <p
                            v-if="form.errors.observacoes"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.observacoes }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="form.processing">
                            Criar 13º Salário
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
