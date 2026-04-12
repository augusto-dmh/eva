<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowUpRight,
    Bot,
    CalendarDays,
    Clock,
    FileWarning,
    Gift,
    Receipt,
    TrendingUp,
    Trophy,
    Users,
} from 'lucide-vue-next';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

interface CollaboratorStats {
    total: number;
    clt: number;
    pj: number;
    estagiario: number;
    socio: number;
    novos_mes: number;
}

interface PayrollSummary {
    mes_referencia: string;
    status: string;
    status_label: string;
    liquido: number;
    salarios_brutos: number;
    pj: number;
    id: number;
}

interface DissidioSummary { ano: number; percentual: number; status: string; label: string }
interface PlrSummary { ano: number; status: string; label: string; valor_total: number | null }
interface ThirteenthSummary { ano: number; status: string; label: string }
interface ActivityItem { texto: string; tipo: 'info' | 'success' | 'warn'; data: string }

const props = defineProps<{
    collaboratorStats: CollaboratorStats;
    payrollSummary: PayrollSummary | null;
    pendingPjInvoices: number;
    activeVacationBatches: number;
    nextVacationBatch: string | null;
    dissidioSummary: DissidioSummary | null;
    thirteenthSummary: ThirteenthSummary | null;
    plrSummary: PlrSummary | null;
    recentActivity: ActivityItem[];
}>();

const page = usePage();
const firstName = computed(() => (page.props.auth.user.name as string).split(' ')[0]);

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 }).format(value);

const payrollStatusColor = computed(() => {
    const map: Record<string, string> = {
        aberto: '#0096ca',
        aguardando_nf_pj: '#f59e0b',
        aguardando_comissoes: '#f97316',
        em_revisao: '#8b5cf6',
        conferido_contabilidade: '#10b981',
        fechado: '#475569',
    };
    return map[props.payrollSummary?.status ?? ''] ?? '#0096ca';
});

const kpis = computed(() => [
    {
        label: 'Colaboradores',
        value: String(props.collaboratorStats.total),
        sub: `${props.collaboratorStats.clt} CLT · ${props.collaboratorStats.pj} PJ · ${props.collaboratorStats.estagiario} Est.`,
        icon: Users,
        color: '#0096ca',
        trend: props.collaboratorStats.novos_mes > 0 ? `+${props.collaboratorStats.novos_mes} este mês` : 'Sem novos este mês',
        href: '/collaborators',
    },
    {
        label: 'Lotes de Férias',
        value: String(props.activeVacationBatches),
        sub: props.nextVacationBatch ? `Próximo: ${props.nextVacationBatch}` : 'Nenhum pendente',
        icon: CalendarDays,
        color: '#f59e0b',
        trend: props.activeVacationBatches > 0 ? 'Em andamento' : 'Tudo em dia',
        href: '/vacation-batches',
    },
    {
        label: props.dissidioSummary ? `Dissídio ${props.dissidioSummary.ano}` : 'Dissídio',
        value: props.dissidioSummary ? `${props.dissidioSummary.percentual}%` : '—',
        sub: props.dissidioSummary?.label ?? 'Sem dados',
        icon: TrendingUp,
        color: '#10b981',
        trend: props.dissidioSummary?.label ?? '—',
        href: '/dissidio-rounds',
    },
    {
        label: props.plrSummary ? `PLR ${props.plrSummary.ano}` : 'PLR',
        value: props.plrSummary?.valor_total ? formatCurrency(props.plrSummary.valor_total) : '—',
        sub: props.plrSummary?.label ?? 'Sem dados',
        icon: Trophy,
        color: '#a78bfa',
        trend: props.plrSummary?.label ?? '—',
        href: '/plr',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-8 p-6 pb-12">

        <!-- Hero -->
        <div class="fade-up fade-up-1 flex items-end justify-between">
            <div>
                <p class="mb-1 text-xs font-medium uppercase tracking-widest text-muted-foreground">
                    Folha de Pagamento
                    <span v-if="payrollSummary"> · {{ payrollSummary.mes_referencia }}</span>
                </p>
                <h1 class="font-display gradient-text stat-number text-5xl font-extrabold">
                    {{ payrollSummary ? formatCurrency(payrollSummary.liquido) : 'Nenhum ciclo aberto' }}
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    <template v-if="payrollSummary">
                        Ciclo <span class="font-medium text-foreground">{{ payrollSummary.status_label }}</span>
                        <template v-if="pendingPjInvoices > 0">
                            · <span class="font-medium" style="color:#f59e0b;">{{ pendingPjInvoices }} NF PJ pendente{{ pendingPjInvoices > 1 ? 's' : '' }}</span>
                        </template>
                    </template>
                    <template v-else>Nenhum ciclo ativo no momento</template>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    v-if="pendingPjInvoices > 0 && payrollSummary"
                    :href="`/payroll-cycles/${payrollSummary.id}`"
                    class="flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-opacity hover:opacity-80"
                    style="background:rgba(245,158,11,0.08);border-color:rgba(245,158,11,0.25);color:#f59e0b;"
                >
                    <FileWarning class="size-3" />
                    {{ pendingPjInvoices }} NF pendente{{ pendingPjInvoices > 1 ? 's' : '' }}
                </Link>
                <div
                    v-if="payrollSummary"
                    class="flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium"
                    :style="{ background: `${payrollStatusColor}14`, borderColor: `${payrollStatusColor}33`, color: payrollStatusColor }"
                >
                    <span class="pulse-dot inline-block h-1.5 w-1.5 rounded-full" :style="{ background: payrollStatusColor }" />
                    {{ payrollSummary.status_label }}
                </div>
            </div>
        </div>

        <div class="h-px w-full" style="background:linear-gradient(90deg,rgba(0,150,202,0.25) 0%,transparent 80%);" />

        <!-- KPI grid -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Link
                v-for="(kpi, i) in kpis"
                :key="kpi.label"
                :href="kpi.href"
                :class="`fade-up fade-up-${i + 2} glass-card flex flex-col gap-4 p-5 transition-opacity hover:opacity-90`"
            >
                <div class="flex items-start justify-between">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">{{ kpi.label }}</p>
                    <component :is="kpi.icon" class="size-4 shrink-0 opacity-60" :style="{ color: kpi.color }" />
                </div>
                <div>
                    <div class="stat-number text-3xl" :style="{ color: kpi.color }">{{ kpi.value }}</div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ kpi.sub }}</p>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <ArrowUpRight class="size-3 opacity-50" />
                    {{ kpi.trend }}
                </div>
            </Link>
        </div>

        <!-- 13th salary banner -->
        <div
            v-if="thirteenthSummary && thirteenthSummary.status !== 'concluido'"
            class="fade-up fade-up-6 glass-card flex items-center justify-between p-4"
        >
            <div class="flex items-center gap-3">
                <Gift class="size-5" style="color:#f59e0b;" />
                <div>
                    <p class="text-sm font-medium text-foreground">13° Salário {{ thirteenthSummary.ano }}</p>
                    <p class="text-xs text-muted-foreground">{{ thirteenthSummary.label }}</p>
                </div>
            </div>
            <Link href="/thirteenth-salary" class="rounded-lg px-3 py-1.5 text-xs font-medium transition-opacity hover:opacity-80" style="background:rgba(245,158,11,0.12);color:#f59e0b;">
                Ver detalhes
            </Link>
        </div>

        <!-- Bottom row -->
        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">

            <!-- Activity feed -->
            <div class="fade-up fade-up-7 glass-card p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wider text-foreground">Atividade Recente</h2>
                    <Clock class="size-4 text-muted-foreground" />
                </div>
                <div v-if="recentActivity.length > 0" class="flex flex-col gap-3">
                    <div v-for="item in recentActivity" :key="item.texto" class="flex items-start gap-3 text-sm">
                        <div class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full"
                             :style="{ background: item.tipo === 'success' ? 'rgba(16,185,129,0.1)' : item.tipo === 'warn' ? 'rgba(245,158,11,0.1)' : 'rgba(0,150,202,0.1)' }">
                            <div class="size-1.5 rounded-full"
                                 :style="{ background: item.tipo === 'success' ? '#10b981' : item.tipo === 'warn' ? '#f59e0b' : '#0096ca' }" />
                        </div>
                        <div class="flex-1">
                            <p class="text-foreground">{{ item.texto }}</p>
                            <p class="text-xs text-muted-foreground">{{ item.data }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">Nenhuma atividade registrada ainda.</p>
            </div>

            <!-- DP Assistant entry point -->
            <Link href="/dp-assistant" class="fade-up fade-up-8 glass-card group flex flex-col gap-4 p-5 transition-all hover:opacity-90">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl" style="background:linear-gradient(135deg,rgba(0,150,202,0.2),rgba(0,30,98,0.3));border:1px solid rgba(0,150,202,0.3);">
                        <Bot class="size-5" style="color:#0096ca;" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-foreground">Assistente de DP</p>
                        <p class="text-xs text-muted-foreground">Pergunte sobre folha, férias, PLR...</p>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">
                    IA treinada nas regras trabalhistas brasileiras com acesso aos dados reais do sistema.
                </p>
                <div class="flex flex-wrap gap-2">
                    <span v-for="chip in ['Elegibilidade férias', 'Cálculo 13°', 'DSR comissões']" :key="chip"
                          class="rounded-full px-2.5 py-1 text-xs"
                          style="background:rgba(0,150,202,0.08);color:#0096ca;border:1px solid rgba(0,150,202,0.2);">
                        {{ chip }}
                    </span>
                </div>
                <div class="mt-auto flex items-center gap-1.5 text-xs" style="color:#0096ca;">
                    <ArrowUpRight class="size-3 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                    Abrir assistente
                </div>
            </Link>
        </div>
    </div>
</template>
