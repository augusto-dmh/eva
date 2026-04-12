<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Bot,
    CalendarDays,
    FileText,
    Gift,
    HandCoins,
    LayoutDashboard,
    Receipt,
    TrendingUp,
    Trophy,
    User,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as collaboratorsIndex } from '@/routes/collaborators';
import { index as payrollCyclesIndex } from '@/routes/payroll-cycles';
import { profile as selfServiceProfile } from '@/routes/self-service';
import { index as vacationBatchesIndex } from '@/routes/vacation-batches';
import type { NavItem } from '@/types';

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);
const isPj = computed(() => page.props.auth.isPj);

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutDashboard,
    },
    {
        title: 'Colaboradores',
        href: collaboratorsIndex(),
        icon: Users,
    },
    {
        title: 'Folha de Pagamento',
        href: payrollCyclesIndex(),
        icon: Receipt,
    },
    {
        title: 'Férias',
        href: vacationBatchesIndex(),
        icon: CalendarDays,
    },
    {
        title: 'Dissídio',
        href: '/dissidio-rounds',
        icon: TrendingUp,
    },
    {
        title: '13° Salário',
        href: '/thirteenth-salary',
        icon: Gift,
    },
    {
        title: 'PLR',
        href: '/plr',
        icon: Trophy,
    },
    {
        title: 'Contribuição Assistencial',
        href: '/union/opposition',
        icon: HandCoins,
    },
    {
        title: 'Assistente de DP',
        href: '/dp-assistant',
        icon: Bot,
    },
];

const baseCollaboratorNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutDashboard,
    },
    {
        title: 'Meu Perfil',
        href: selfServiceProfile(),
        icon: User,
    },
    {
        title: 'Assistente de DP',
        href: '/dp-assistant',
        icon: Bot,
    },
];

const mainNavItems = computed<NavItem[]>(() => {
    if (userRole.value === 'admin') {
        return adminNavItems;
    }

    const items = [...baseCollaboratorNavItems];

    if (isPj.value) {
        items.push({
            title: 'Notas Fiscais',
            href: '/self-service/invoices',
            icon: FileText,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <div
                                class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-lg"
                                style="
                                    background: linear-gradient(
                                        135deg,
                                        #1c55b1 0%,
                                        rgba(59, 130, 246, 0.75) 100%
                                    );
                                    box-shadow: 0 0 16px
                                        rgba(59, 130, 246, 0.25);
                                "
                            >
                                <span
                                    class="text-sm font-bold text-white"
                                    style="font-family: 'Syne', sans-serif"
                                    >E</span
                                >
                            </div>
                            <div class="ml-1 grid flex-1 text-left">
                                <span
                                    class="truncate font-extrabold text-white"
                                    style="
                                        font-family: 'Syne', sans-serif;
                                        font-size: 0.75rem;
                                        letter-spacing: 0.12em;
                                    "
                                >
                                    EVA
                                </span>
                                <span
                                    class="truncate text-xs"
                                    style="
                                        color: rgba(148, 163, 184, 0.6);
                                        letter-spacing: 0.02em;
                                    "
                                >
                                    Departamento Pessoal
                                </span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
