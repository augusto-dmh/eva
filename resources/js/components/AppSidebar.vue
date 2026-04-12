<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, User, Users } from 'lucide-vue-next';
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
import { profile as selfServiceProfile } from '@/routes/self-service';
import type { NavItem } from '@/types';

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);

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
];

const collaboratorNavItems: NavItem[] = [
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

const mainNavItems = computed<NavItem[]>(() =>
    userRole.value === 'admin' ? adminNavItems : collaboratorNavItems,
);

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
