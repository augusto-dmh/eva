<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, nextTick, computed } from 'vue';
import { marked } from 'marked';
import {
    ArrowLeft,
    Bot,
    CalendarDays,
    ChevronRight,
    Clock,
    Copy,
    Check,
    Gift,
    HandCoins,
    Receipt,
    Send,
    Sparkles,
    TrendingUp,
    Trophy,
    Users,
    Zap,
} from 'lucide-vue-next';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Assistente de DP', href: '/dp-assistant' },
        ],
    },
});

// ── Types ────────────────────────────────────────────────────────────────────
interface Message {
    id: number;
    role: 'user' | 'assistant';
    text: string;
    timestamp: Date;
    copied?: boolean;
}

// ── State ────────────────────────────────────────────────────────────────────
const messages = ref<Message[]>([]);
const inputText = ref('');
const loading = ref(false);
const error = ref('');
const chatContainer = ref<HTMLDivElement | null>(null);
const conversationId = ref<string | null>(null);
let messageIdCounter = 0;

function startNewConversation() {
    conversationId.value = null;
    messages.value = [];
    error.value = '';
}

// ── Capabilities: System-specific (tool-backed) ─────────────────────────────
const systemCapabilities = [
    {
        icon: CalendarDays,
        color: '#f59e0b',
        bg: 'rgba(245,158,11,0.08)',
        border: 'rgba(245,158,11,0.2)',
        title: 'Elegibilidade de Férias',
        description: 'Consulte quem está elegível, períodos aquisitivos e lotes ativos.',
        example: 'Quais colaboradores CLT completam 12 meses de período aquisitivo em outubro?',
    },
    {
        icon: Clock,
        color: '#10b981',
        bg: 'rgba(16,185,129,0.08)',
        border: 'rgba(16,185,129,0.2)',
        title: 'Status da Folha',
        description: 'Ciclos de pagamento, valores brutos/líquidos e notas fiscais PJ pendentes.',
        example: 'Qual o status da folha atual e quantas NFs PJ estão pendentes?',
    },
    {
        icon: Users,
        color: '#0096ca',
        bg: 'rgba(0,150,202,0.08)',
        border: 'rgba(0,150,202,0.2)',
        title: 'Estatísticas de Colaboradores',
        description: 'Headcount por tipo de contrato, departamento, admissões e desligamentos recentes.',
        example: 'Quantos colaboradores CLT, PJ e estagiários temos atualmente?',
    },
];

// ── Capabilities: General knowledge (labor law) ─────────────────────────────
const generalCapabilities = [
    {
        icon: Gift,
        color: '#10b981',
        bg: 'rgba(16,185,129,0.08)',
        border: 'rgba(16,185,129,0.2)',
        title: '13° Salário',
        description: 'Cálculo pro-rata, média de comissões, parcelas e deduções INSS/IRRF.',
        example: 'Como calcular o 13° salário de um colaborador admitido em maio com salário de R$ 5.000 e média de comissões de R$ 2.400?',
    },
    {
        icon: Receipt,
        color: '#0096ca',
        bg: 'rgba(0,150,202,0.08)',
        border: 'rgba(0,150,202,0.2)',
        title: 'Comissões e DSR',
        description: 'Regras de DSR sobre comissões, cálculo para Closers e Advisors.',
        example: 'Como calcular o DSR para um Closer que recebeu R$ 8.000 em comissões brutas em um mês com 22 dias úteis?',
    },
    {
        icon: TrendingUp,
        color: '#8b5cf6',
        bg: 'rgba(139,92,246,0.08)',
        border: 'rgba(139,92,246,0.2)',
        title: 'Dissídio Coletivo',
        description: 'Retroativos, cálculo do diferencial por competência, aplicação e INSS/FGTS.',
        example: 'O dissídio de 4,81% foi aplicado em janeiro com data-base em setembro. Quantas competências de retroativo e como calcular o abono pecuniário?',
    },
    {
        icon: HandCoins,
        color: '#f97316',
        bg: 'rgba(249,115,22,0.08)',
        border: 'rgba(249,115,22,0.2)',
        title: 'Contribuição Assistencial',
        description: 'Prazos, valor (2 dias de salário), parcelamento em 4x e registro de oposição.',
        example: 'Qual é o prazo para um colaborador registrar oposição à contribuição assistencial? Como deve ser feito e o que comprova?',
    },
    {
        icon: Trophy,
        color: '#a78bfa',
        bg: 'rgba(167,139,250,0.08)',
        border: 'rgba(167,139,250,0.2)',
        title: 'PLR — Participação nos Lucros',
        description: 'Distribuição proporcional por salário e tempo, IRRF específico de PLR.',
        example: 'Como funciona o cálculo proporcional do PLR por tempo de casa e salário? Qual a alíquota de IRRF para um valor de R$ 8.500?',
    },
];

// ── Suggestion chips (shown when chat is empty) ───────────────────────────────
const systemSuggestions = [
    'Quem está elegível para férias agora?',
    'Qual o status da folha atual?',
    'Quantos colaboradores CLT temos?',
];

const generalSuggestions = [
    'Como funciona o DSR sobre comissões?',
    'Explique o cálculo do 13° salário',
    'Quais as faixas de INSS 2025?',
];

// ── Chat logic ────────────────────────────────────────────────────────────────
function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

async function sendMessage(text?: string) {
    const question = (text ?? inputText.value).trim();
    if (!question || loading.value) return;

    inputText.value = '';
    error.value = '';

    messages.value.push({
        id: ++messageIdCounter,
        role: 'user',
        text: question,
        timestamp: new Date(),
    });

    await scrollToBottom();
    loading.value = true;

    try {
        const response = await fetch('/dp-assistant/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ question, conversation_id: conversationId.value }),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        conversationId.value = data.conversation_id ?? conversationId.value;
        messages.value.push({
            id: ++messageIdCounter,
            role: 'assistant',
            text: data.answer ?? 'Não foi possível obter uma resposta.',
            timestamp: new Date(),
        });
    } catch {
        error.value = 'Erro ao conectar ao assistente. Verifique se AI_DEFAULT_PROVIDER e a chave da API estão configurados corretamente no .env.';
    } finally {
        loading.value = false;
        await scrollToBottom();
    }
}

async function scrollToBottom() {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
}

async function copyText(msg: Message) {
    await navigator.clipboard.writeText(msg.text);
    msg.copied = true;
    setTimeout(() => { msg.copied = false; }, 2000);
}

function formatTime(date: Date): string {
    return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

// Configure marked for clean markdown rendering
marked.setOptions({
    breaks: true,
    gfm: true,
});

function renderMarkdown(text: string): string {
    return marked.parse(text) as string;
}

const isEmpty = computed(() => messages.value.length === 0 && !loading.value);
</script>

<template>
    <Head title="Assistente de DP" />

    <div class="flex min-h-0 flex-1 flex-col overflow-hidden" style="height:calc(100vh - 3.5rem);">

        <!-- Two-column layout -->
        <div class="grid min-h-0 flex-1 grid-cols-1 overflow-hidden lg:grid-cols-[380px_1fr]">

            <!-- ── Left panel: capabilities ──────────────────────────────── -->
            <div class="hidden min-h-0 flex-col gap-0 overflow-hidden border-r border-border/30 lg:flex" style="background:var(--sidebar);">

                <!-- Panel header -->
                <div class="border-b border-border/30 p-6">
                    <div class="mb-3 flex size-12 items-center justify-center rounded-2xl"
                         style="background:linear-gradient(135deg,rgba(0,150,202,0.25),rgba(0,30,98,0.4));border:1px solid rgba(0,150,202,0.3);">
                        <Bot class="size-6" style="color:#0096ca;" />
                    </div>
                    <h1 class="text-xl font-bold text-foreground">Assistente de DP</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        IA com acesso ao sistema Eva e conhecimento das regras trabalhistas brasileiras.
                    </p>
                    <div class="mt-3 flex items-center gap-1.5 text-xs" style="color:#0096ca;">
                        <Zap class="size-3" />
                        Alimentado por IA via Laravel AI SDK
                    </div>
                </div>

                <!-- Capability cards -->
                <div class="flex-1 overflow-y-auto p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        Consultas ao Sistema Eva
                    </p>
                    <div class="flex flex-col gap-2">
                        <button
                            v-for="cap in systemCapabilities"
                            :key="cap.title"
                            class="group w-full rounded-xl border p-3 text-left transition-all hover:opacity-90"
                            :style="{ background: cap.bg, borderColor: cap.border }"
                            @click="sendMessage(cap.example)"
                        >
                            <div class="flex items-center gap-2">
                                <component :is="cap.icon" class="size-4 shrink-0" :style="{ color: cap.color }" />
                                <span class="text-sm font-medium text-foreground">{{ cap.title }}</span>
                                <ChevronRight class="ml-auto size-3 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" />
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">{{ cap.description }}</p>
                        </button>
                    </div>

                    <p class="mb-3 mt-5 text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        Conhecimento Trabalhista
                    </p>
                    <div class="flex flex-col gap-2">
                        <button
                            v-for="cap in generalCapabilities"
                            :key="cap.title"
                            class="group w-full rounded-xl border p-3 text-left transition-all hover:opacity-90"
                            :style="{ background: cap.bg, borderColor: cap.border }"
                            @click="sendMessage(cap.example)"
                        >
                            <div class="flex items-center gap-2">
                                <component :is="cap.icon" class="size-4 shrink-0" :style="{ color: cap.color }" />
                                <span class="text-sm font-medium text-foreground">{{ cap.title }}</span>
                                <ChevronRight class="ml-auto size-3 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" />
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">{{ cap.description }}</p>
                        </button>
                    </div>
                </div>

                <!-- Back link -->
                <div class="border-t border-border/30 p-4">
                    <Link
                        :href="dashboard()"
                        class="flex items-center gap-2 text-xs text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft class="size-3" />
                        Voltar ao Dashboard
                    </Link>
                </div>
            </div>

            <!-- ── Right panel: chat ──────────────────────────────────────── -->
            <div class="flex min-h-0 flex-col overflow-hidden">

                <!-- Chat header with "Nova conversa" -->
                <div v-if="messages.length > 0" class="flex items-center justify-between border-b border-border/30 px-6 py-3">
                    <span class="text-xs text-muted-foreground">
                        {{ messages.length }} {{ messages.length === 1 ? 'mensagem' : 'mensagens' }}
                    </span>
                    <button
                        class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs transition-all hover:opacity-80"
                        style="background:rgba(0,150,202,0.06);border-color:rgba(0,150,202,0.2);color:#0096ca;"
                        @click="startNewConversation"
                    >
                        <Sparkles class="size-3" />
                        Nova conversa
                    </button>
                </div>

                <!-- Chat messages -->
                <div
                    ref="chatContainer"
                    class="min-h-0 flex-1 overflow-y-auto p-6"
                    style="scroll-behavior:smooth;"
                >

                    <!-- Empty state -->
                    <div v-if="isEmpty" class="flex h-full flex-col items-center justify-center gap-6">
                        <div class="flex size-20 items-center justify-center rounded-3xl"
                             style="background:linear-gradient(135deg,rgba(0,150,202,0.15),rgba(0,30,98,0.25));border:1px solid rgba(0,150,202,0.25);">
                            <Sparkles class="size-10" style="color:#0096ca;" />
                        </div>
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-foreground">Olá! Como posso ajudar?</h2>
                            <p class="mt-2 max-w-md text-sm text-muted-foreground">
                                Pergunte sobre folha de pagamento, férias, 13° salário, PLR, dissídio
                                ou qualquer dúvida de Departamento Pessoal.
                            </p>
                        </div>
                        <!-- Suggestion chips -->
                        <div class="flex w-full max-w-lg flex-col gap-3">
                            <div>
                                <p class="mb-2 text-center text-xs font-medium text-muted-foreground">Consultas ao Sistema</p>
                                <div class="flex flex-wrap justify-center gap-2">
                                    <button
                                        v-for="s in systemSuggestions"
                                        :key="s"
                                        class="rounded-full border px-3 py-1.5 text-xs transition-all hover:opacity-80"
                                        style="background:rgba(0,150,202,0.06);border-color:rgba(0,150,202,0.2);color:#0096ca;"
                                        @click="sendMessage(s)"
                                    >
                                        {{ s }}
                                    </button>
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-center text-xs font-medium text-muted-foreground">Conhecimento Trabalhista</p>
                                <div class="flex flex-wrap justify-center gap-2">
                                    <button
                                        v-for="s in generalSuggestions"
                                        :key="s"
                                        class="rounded-full border px-3 py-1.5 text-xs transition-all hover:opacity-80"
                                        style="background:rgba(139,92,246,0.06);border-color:rgba(139,92,246,0.2);color:#8b5cf6;"
                                        @click="sendMessage(s)"
                                    >
                                        {{ s }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Mobile capabilities -->
                        <div class="mt-2 w-full max-w-lg lg:hidden">
                            <p class="mb-2 text-xs font-medium text-muted-foreground">Consultas ao Sistema</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="cap in systemCapabilities"
                                    :key="cap.title"
                                    class="rounded-xl border p-3 text-left text-xs transition-all hover:opacity-80"
                                    :style="{ background: cap.bg, borderColor: cap.border }"
                                    @click="sendMessage(cap.example)"
                                >
                                    <component :is="cap.icon" class="mb-1 size-4" :style="{ color: cap.color }" />
                                    <div class="font-medium text-foreground">{{ cap.title }}</div>
                                </button>
                            </div>
                            <p class="mb-2 mt-3 text-xs font-medium text-muted-foreground">Conhecimento Trabalhista</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="cap in generalCapabilities"
                                    :key="cap.title"
                                    class="rounded-xl border p-3 text-left text-xs transition-all hover:opacity-80"
                                    :style="{ background: cap.bg, borderColor: cap.border }"
                                    @click="sendMessage(cap.example)"
                                >
                                    <component :is="cap.icon" class="mb-1 size-4" :style="{ color: cap.color }" />
                                    <div class="font-medium text-foreground">{{ cap.title }}</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div v-else class="mx-auto flex max-w-3xl flex-col gap-6">
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            :class="['flex gap-3', msg.role === 'user' ? 'justify-end' : 'justify-start']"
                        >
                            <!-- AI avatar -->
                            <div
                                v-if="msg.role === 'assistant'"
                                class="flex size-8 shrink-0 items-center justify-center self-end rounded-xl"
                                style="background:linear-gradient(135deg,rgba(0,150,202,0.2),rgba(0,30,98,0.3));border:1px solid rgba(0,150,202,0.25);"
                            >
                                <Bot class="size-4" style="color:#0096ca;" />
                            </div>

                            <!-- Bubble -->
                            <div :class="['group relative max-w-[80%]', msg.role === 'user' ? 'items-end' : 'items-start', 'flex flex-col gap-1']">
                                <div
                                    :class="[
                                        'rounded-2xl px-4 py-3 text-sm leading-relaxed',
                                        msg.role === 'user'
                                            ? 'rounded-br-sm text-white'
                                            : 'rounded-bl-sm glass-card',
                                    ]"
                                    :style="msg.role === 'user' ? 'background:linear-gradient(135deg,#0096ca,#004d80);' : ''"
                                    v-html="msg.role === 'assistant' ? renderMarkdown(msg.text) : msg.text"
                                />
                                <div class="flex items-center gap-2 px-1">
                                    <span class="text-xs text-muted-foreground">{{ formatTime(msg.timestamp) }}</span>
                                    <button
                                        v-if="msg.role === 'assistant'"
                                        class="opacity-0 transition-opacity group-hover:opacity-100"
                                        :title="msg.copied ? 'Copiado!' : 'Copiar'"
                                        @click="copyText(msg)"
                                    >
                                        <component
                                            :is="msg.copied ? Check : Copy"
                                            class="size-3 text-muted-foreground"
                                            :style="msg.copied ? 'color:#10b981' : ''"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Typing indicator -->
                        <div v-if="loading" class="flex justify-start gap-3">
                            <div class="flex size-8 shrink-0 items-center justify-center self-end rounded-xl"
                                 style="background:linear-gradient(135deg,rgba(0,150,202,0.2),rgba(0,30,98,0.3));border:1px solid rgba(0,150,202,0.25);">
                                <Bot class="size-4" style="color:#0096ca;" />
                            </div>
                            <div class="glass-card flex items-center gap-1.5 rounded-2xl rounded-bl-sm px-4 py-3">
                                <span
                                    v-for="n in 3"
                                    :key="n"
                                    class="size-1.5 rounded-full"
                                    style="background:#0096ca;animation:typing-dot 1.2s ease-in-out infinite;"
                                    :style="{ animationDelay: `${(n - 1) * 0.2}s` }"
                                />
                            </div>
                        </div>

                        <!-- Error -->
                        <div v-if="error" class="rounded-xl border border-red-500/20 bg-red-500/5 p-4 text-sm text-red-400">
                            {{ error }}
                        </div>
                    </div>
                </div>

                <!-- Input bar -->
                <div class="border-t border-border/30 p-4">
                    <div class="mx-auto max-w-3xl">
                        <div
                            class="flex items-end gap-3 rounded-2xl border p-3 transition-colors focus-within:border-[rgba(0,150,202,0.4)]"
                            style="background:var(--card);border-color:rgba(0,150,202,0.15);"
                        >
                            <textarea
                                v-model="inputText"
                                rows="1"
                                placeholder="Pergunte sobre folha, férias, PLR, 13°, dissídio..."
                                class="flex-1 resize-none bg-transparent text-sm text-foreground placeholder-muted-foreground outline-none"
                                style="max-height:120px;overflow-y:auto;"
                                @keydown.enter.exact.prevent="sendMessage()"
                                @keydown.enter.shift.exact="inputText += '\n'"
                                @input="($event.target as HTMLTextAreaElement).style.height = 'auto'; ($event.target as HTMLTextAreaElement).style.height = ($event.target as HTMLTextAreaElement).scrollHeight + 'px'"
                            />
                            <button
                                :disabled="!inputText.trim() || loading"
                                class="flex size-9 shrink-0 items-center justify-center rounded-xl transition-all disabled:opacity-40"
                                :style="inputText.trim() && !loading ? 'background:linear-gradient(135deg,#0096ca,#004d80);' : 'background:rgba(0,150,202,0.15);'"
                                @click="sendMessage()"
                            >
                                <Send class="size-4" :style="inputText.trim() && !loading ? 'color:#fff;' : 'color:#0096ca;'" />
                            </button>
                        </div>
                        <p class="mt-2 text-center text-xs text-muted-foreground">
                            Enter para enviar · Shift+Enter para nova linha
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes typing-dot {
    0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
    30% { opacity: 1; transform: translateY(-3px); }
}

/* Markdown rendered inside assistant chat bubbles */
:deep(.glass-card) {
    h1, h2, h3, h4 {
        font-weight: 700;
        margin-top: 0.75em;
        margin-bottom: 0.35em;
        color: var(--foreground);
    }
    h1 { font-size: 1.15em; }
    h2 { font-size: 1.08em; }
    h3 { font-size: 1em; }

    p { margin: 0.4em 0; }

    ul, ol {
        padding-left: 1.3em;
        margin: 0.4em 0;
    }
    li { margin: 0.15em 0; }

    strong { color: var(--foreground); }

    code {
        background: rgba(0,150,202,0.12);
        padding: 0.1em 0.35em;
        border-radius: 4px;
        font-size: 0.85em;
    }

    pre {
        background: rgba(0,0,0,0.2);
        padding: 0.75em;
        border-radius: 8px;
        overflow-x: auto;
        margin: 0.5em 0;
    }
    pre code {
        background: none;
        padding: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 0.5em 0;
        font-size: 0.85em;
    }
    th, td {
        border: 1px solid rgba(0,150,202,0.15);
        padding: 0.35em 0.6em;
        text-align: left;
    }
    th {
        background: rgba(0,150,202,0.08);
        font-weight: 600;
    }

    blockquote {
        border-left: 3px solid rgba(0,150,202,0.3);
        padding-left: 0.75em;
        margin: 0.5em 0;
        color: var(--muted-foreground);
    }

    /* Remove trailing margin on last element */
    > :last-child { margin-bottom: 0; }
    > :first-child { margin-top: 0; }
}
</style>
