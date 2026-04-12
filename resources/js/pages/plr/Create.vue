<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'PLR', href: '/plr' },
            { title: 'Nova PLR', href: '#' },
        ],
    },
});

const form = useForm({
    ano_referencia: new Date().getFullYear(),
    observacoes: '',
});

function submit() {
    form.transform((data) => ({
        ano_referencia: data.ano_referencia,
        observacoes: data.observacoes || null,
    })).post('/plr');
}
</script>

<template>
    <Head title="Nova PLR" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Nova PLR</h1>
            <Button variant="outline" as-child>
                <Link href="/plr">Voltar</Link>
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
                        <Label for="observacoes">Observações (opcional)</Label>
                        <textarea
                            id="observacoes"
                            v-model="form.observacoes"
                            class="min-h-[80px] rounded-md border px-3 py-2 text-sm"
                            placeholder="Observações sobre esta rodada de PLR..."
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
                            Criar PLR
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
