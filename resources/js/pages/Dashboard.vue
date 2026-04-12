<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Users, Receipt, CalendarDays, TrendingUp } from 'lucide-vue-next';
import DpAssistantWidget from '@/components/DpAssistantWidget.vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const page = usePage();
const userName = computed(() => page.props.auth.user.name.split(' ')[0]);

const stats = [
    { label: 'Colaboradores Ativos', value: '22', change: '+2 este mês', icon: Users, color: 'text-blue-400' },
    { label: 'Folha do Mês', value: 'R$ 185.000', change: 'Mar/2025 em andamento', icon: Receipt, color: 'text-emerald-400' },
    { label: 'Férias Pendentes', value: '8', change: 'Elegíveis para aprovação', icon: CalendarDays, color: 'text-amber-400' },
    { label: 'Dissídio 2025', value: '4,5%', change: 'Aplicado em Mar/2025', icon: TrendingUp, color: 'text-violet-400' },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Greeting -->
        <div>
            <h1 class="text-2xl font-bold text-foreground">
                Olá, {{ userName }} 👋
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Aqui está o resumo do Departamento Pessoal.
            </p>
        </div>

        <!-- Stat cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="glass-card flex flex-col gap-3 p-5"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        {{ stat.label }}
                    </span>
                    <component :is="stat.icon" :class="['size-4', stat.color]" />
                </div>
                <div class="text-2xl font-bold text-foreground">{{ stat.value }}</div>
                <div class="text-xs text-muted-foreground">{{ stat.change }}</div>
            </div>
        </div>

        <!-- DP Assistant -->
        <DpAssistantWidget />
    </div>
</template>
