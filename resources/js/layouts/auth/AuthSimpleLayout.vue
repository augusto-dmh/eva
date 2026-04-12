<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden p-6 md:p-10"
        style="background: #020617;"
    >
        <!-- Ambient glow -->
        <div
            class="pointer-events-none absolute"
            style="width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(59, 130, 246, 0.12) 0%, transparent 70%); filter: blur(80px); top: -200px; left: 50%; transform: translateX(-50%);"
        />

        <!-- Grid bg -->
        <div
            class="pointer-events-none absolute inset-0"
            style="background-image: linear-gradient(rgba(59,130,246,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(59,130,246,0.05) 1px, transparent 1px); background-size: 60px 60px; mask-image: radial-gradient(circle at center, rgba(0,0,0,0.5) 0%, transparent 80%);"
        />

        <div class="relative z-10 w-full max-w-sm">
            <!-- Logo & brand -->
            <div class="mb-8 flex flex-col items-center gap-3">
                <div
                    class="flex size-12 items-center justify-center rounded-xl"
                    style="background: linear-gradient(135deg, rgba(28, 85, 177, 0.95), rgba(59, 130, 246, 0.8)); box-shadow: 0 0 24px rgba(59, 130, 246, 0.4);"
                >
                    <span class="text-lg font-bold text-white">E</span>
                </div>
                <div class="text-center">
                    <div class="text-sm font-bold tracking-widest text-white" style="letter-spacing: 0.12em;">EVA</div>
                    <div class="text-xs" style="color: rgba(148, 163, 184, 0.7);">Departamento Pessoal</div>
                </div>
            </div>

            <!-- Card -->
            <div
                class="rounded-2xl p-8"
                style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(24px); border: 1px solid rgba(59, 130, 246, 0.15);"
            >
                <div v-if="title || description" class="mb-6 text-center">
                    <h1 v-if="title" class="text-xl font-semibold text-white">{{ title }}</h1>
                    <p v-if="description" class="mt-1 text-sm" style="color: #94a3b8;">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
