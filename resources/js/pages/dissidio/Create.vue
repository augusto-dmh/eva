<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dissídio', href: '/dissidio-rounds' },
            { title: 'Novo Dissídio', href: '#' },
        ],
    },
});

const form = useForm({
    ano_referencia: new Date().getFullYear(),
    data_base: '',
    percentual_display: '',
    aplica_estagiarios: false,
    observacoes: '',
});

function submit() {
    const percentual = parseFloat(form.percentual_display) / 100;

    form.transform((data) => ({
        ano_referencia: data.ano_referencia,
        data_base: data.data_base,
        percentual,
        aplica_estagiarios: data.aplica_estagiarios,
        observacoes: data.observacoes || null,
    })).post('/dissidio-rounds');
}
</script>

<template>
    <Head title="Novo Dissídio" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Novo Dissídio</h1>
            <Button variant="outline" as-child>
                <Link href="/dissidio-rounds">Voltar</Link>
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
                        <Label for="data_base">Data Base</Label>
                        <Input
                            id="data_base"
                            v-model="form.data_base"
                            type="date"
                            required
                        />
                        <p
                            v-if="form.errors.data_base"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.data_base }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <Label for="percentual">
                            Percentual (ex: 5.5 para 5,5%)
                        </Label>
                        <Input
                            id="percentual"
                            v-model="form.percentual_display"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            placeholder="5.5"
                            required
                        />
                        <p
                            v-if="form.errors.percentual_display"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.percentual_display }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="aplica_estagiarios"
                            v-model="form.aplica_estagiarios"
                            type="checkbox"
                            class="h-4 w-4 rounded border"
                        />
                        <Label for="aplica_estagiarios">
                            Aplicar a estagiários
                        </Label>
                    </div>

                    <div class="flex flex-col gap-1">
                        <Label for="observacoes">Observações (opcional)</Label>
                        <textarea
                            id="observacoes"
                            v-model="form.observacoes"
                            class="min-h-[80px] rounded-md border px-3 py-2 text-sm"
                            placeholder="Observações sobre esta rodada de dissídio..."
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
                            Criar Dissídio
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
