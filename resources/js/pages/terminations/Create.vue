<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Collaborator, EnumOption } from '@/types/collaborator';

type Props = {
    collaborator: Collaborator;
    terminationTypes: EnumOption[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Colaboradores', href: '/collaborators' },
            { title: 'Iniciar Rescisão', href: '#' },
        ],
    },
});

const form = useForm({
    tipo_desligamento: '',
    data_comunicacao: '',
    data_efetivacao: '',
    motivo: '',
});

function submit() {
    form.post(`/collaborators/${props.collaborator.id}/termination`);
}
</script>

<template>
    <Head title="Iniciar Rescisão" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Iniciar Rescisão</h1>
            <Button variant="outline" as-child>
                <Link :href="`/collaborators/${collaborator.id}`"
                    >Cancelar</Link
                >
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>{{ collaborator.nome_completo }}</CardTitle>
            </CardHeader>
            <CardContent>
                <form class="flex flex-col gap-4" @submit.prevent="submit">
                    <!-- Tipo de Desligamento -->
                    <div class="flex flex-col gap-1">
                        <label
                            class="text-sm font-medium"
                            for="tipo_desligamento"
                        >
                            Tipo de Desligamento
                            <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="tipo_desligamento"
                            v-model="form.tipo_desligamento"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                            required
                        >
                            <option value="" disabled>Selecione...</option>
                            <option
                                v-for="t in terminationTypes"
                                :key="t.value"
                                :value="t.value"
                            >
                                {{ t.label }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.tipo_desligamento"
                            class="text-sm text-red-500"
                        >
                            {{ form.errors.tipo_desligamento }}
                        </p>
                    </div>

                    <!-- Data de Comunicação -->
                    <div class="flex flex-col gap-1">
                        <label
                            class="text-sm font-medium"
                            for="data_comunicacao"
                        >
                            Data de Comunicação
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="data_comunicacao"
                            v-model="form.data_comunicacao"
                            type="date"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                            required
                        />
                        <p
                            v-if="form.errors.data_comunicacao"
                            class="text-sm text-red-500"
                        >
                            {{ form.errors.data_comunicacao }}
                        </p>
                    </div>

                    <!-- Data de Efetivação -->
                    <div class="flex flex-col gap-1">
                        <label
                            class="text-sm font-medium"
                            for="data_efetivacao"
                        >
                            Data de Efetivação
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="data_efetivacao"
                            v-model="form.data_efetivacao"
                            type="date"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                            required
                        />
                        <p
                            v-if="form.errors.data_efetivacao"
                            class="text-sm text-red-500"
                        >
                            {{ form.errors.data_efetivacao }}
                        </p>
                    </div>

                    <!-- Motivo -->
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium" for="motivo"
                            >Motivo</label
                        >
                        <textarea
                            id="motivo"
                            v-model="form.motivo"
                            rows="3"
                            maxlength="1000"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                        />
                        <p
                            v-if="form.errors.motivo"
                            class="text-sm text-red-500"
                        >
                            {{ form.errors.motivo }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Button variant="outline" as-child>
                            <Link :href="`/collaborators/${collaborator.id}`"
                                >Cancelar</Link
                            >
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            Iniciar Rescisão
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
