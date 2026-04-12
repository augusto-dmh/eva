<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
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
                                class="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-primary-foreground"
                            >
                                <span class="text-xs font-bold">E</span>
                            </div>
                            <div class="ml-1 grid flex-1 text-left text-sm">
                                <span
                                    class="truncate font-semibold text-primary"
                                    >EVA</span
                                >
                                <span
                                    class="truncate text-xs text-muted-foreground"
                                    >Portal do Colaborador</span
                                >
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
