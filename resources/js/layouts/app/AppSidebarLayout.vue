<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

withDefaults(defineProps<{ breadcrumbs?: BreadcrumbItem[] }>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <!-- Ambient glows -->
    <div
        class="glow-primary pointer-events-none"
        style="top: -200px; left: -200px; opacity: 0.6"
    />
    <div
        class="glow-primary pointer-events-none"
        style="
            bottom: -200px;
            right: -200px;
            background: radial-gradient(
                circle,
                rgba(99, 102, 241, 0.1) 0%,
                transparent 70%
            );
            opacity: 0.5;
        "
    />

    <AppShell variant="sidebar" class="app-grid-bg">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <Toaster />
    </AppShell>
</template>
