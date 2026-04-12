<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(defineProps<{ breadcrumbs?: BreadcrumbItem[] }>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <header
        class="flex h-14 shrink-0 items-center gap-3 border-b px-6 backdrop-blur-md transition-all ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
        style="
            background: var(--nav-bg, rgba(2, 6, 23, 0.6));
            border-color: var(--border);
        "
    >
        <SidebarTrigger
            class="-ml-1 text-muted-foreground hover:text-foreground"
        />
        <div class="h-4 w-px bg-border" />
        <Breadcrumbs
            v-if="breadcrumbs && breadcrumbs.length > 0"
            :breadcrumbs="breadcrumbs"
        />
    </header>
</template>
