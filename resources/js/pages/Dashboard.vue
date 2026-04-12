<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    AlertCircle,
    ArrowUpRight,
    CalendarDays,
    Clock,
    Receipt,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import DpAssistantWidget from '@/components/DpAssistantWidget.vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const page = usePage();
const firstName = computed(() => page.props.auth.user.name.split(' ')[0]);

const kpis = [
    {
        label: 'Colaboradores',
        value: '22',
        sub: '20 CLT · 2 PJ',
        icon: Users,
        color: '#3b82f6',
        trend: '+2 este mês',
    },
    {
        label: 'Férias Pendentes',
        value: '8',
        sub: 'Aguardando confirmação',
        icon: CalendarDays,
        color: '#f59e0b',
        trend: 'Abrir lote',
    },
    {
        label: 'Dissídio 2025',
        value: '4,5%',
        sub: 'Aplicado em Mar/25',
        icon: TrendingUp,
        color: '#10b981',
        trend: 'Concluído',
    },
    {
        label: 'PLR Simulado',
        value: 'R$ 500k',
        sub: 'Distribuído a 10 CLTs',
        icon: Receipt,
        color: '#a78bfa',
        trend: 'Pendente pagamento',
    },
];

const activity = [
    { time: 'Há 2h', text: 'Folha de Mar/25 aberta', type: 'info' },
    { time: 'Ontem', text: 'Dissídio 4,5% aplicado — 10 históricos criados', type: 'success' },
    { time: '2 dias', text: 'PLR 2025 simulado — R$ 500.000 distribuídos', type: 'success' },
    { time: '5 dias', text: '3 notas fiscais aguardando aprovação', type: 'warn' },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-8 p-6 pb-12">

        <!-- Hero: current cycle focal point -->
        <div class="fade-up fade-up-1 flex items-end justify-between">
            <div>
                <p class="mb-1 text-xs font-medium uppercase tracking-widest text-muted-foreground">
                    Folha de Pagamento · Março 2025
                </p>
                <h1 class="font-display gradient-text stat-number text-5xl font-extrabold">
                    R$ 185.000,00
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Ciclo <span class="font-medium text-foreground">aberto</span> ·
                    Pagamento previsto <span class="font-medium text-foreground">31 Mar</span>
                </p>
            </div>
            <div class="flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium"
                 style="background: rgba(59,130,246,0.08); border-color: rgba(59,130,246,0.2); color: #3b82f6;">
                <span class="pulse-dot inline-block h-1.5 w-1.5 rounded-full bg-blue-400"></span>
                Em andamento
            </div>
        </div>

        <!-- Divider -->
        <div class="h-px w-full" style="background: linear-gradient(90deg, rgba(59,130,246,0.2) 0%, transparent 80%);"></div>

        <!-- KPI grid -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="(kpi, i) in kpis"
                :key="kpi.label"
                :class="`fade-up fade-up-${i + 2} glass-card flex flex-col gap-4 p-5`"
            >
                <div class="flex items-start justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        {{ kpi.label }}
                    </p>
                    <component
                        :is="kpi.icon"
                        class="size-4 shrink-0 opacity-60"
                        :style="{ color: kpi.color }"
                    />
                </div>
                <div>
                    <div class="stat-number text-3xl" :style="{ color: kpi.color }">
                        {{ kpi.value }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ kpi.sub }}</p>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <ArrowUpRight class="size-3 opacity-50" />
                    {{ kpi.trend }}
                </div>
            </div>
        </div>

        <!-- Bottom row: activity + assistant -->
        <div class="grid gap-6 lg:grid-cols-[1fr_400px]">

            <!-- Activity feed -->
            <div class="fade-up fade-up-5 glass-card p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wider text-foreground">
                        Atividade Recente
                    </h2>
                    <Clock class="size-4 text-muted-foreground" />
                </div>
                <div class="flex flex-col gap-3">
                    <div
                        v-for="item in activity"
                        :key="item.text"
                        class="flex items-start gap-3 text-sm"
                    >
                        <div class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full"
                             :style="{
                                 background: item.type === 'success' ? 'rgba(16,185,129,0.1)' :
                                             item.type === 'warn' ? 'rgba(245,158,11,0.1)' :
                                             'rgba(59,130,246,0.1)'
                             }">
                            <div class="size-1.5 rounded-full"
                                 :style="{
                                     background: item.type === 'success' ? '#10b981' :
                                                 item.type === 'warn' ? '#f59e0b' :
                                                 '#3b82f6'
                                 }">
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-foreground">{{ item.text }}</p>
                            <p class="text-xs text-muted-foreground">{{ item.time }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DP Assistant -->
            <DpAssistantWidget />
        </div>
    </div>
</template>
