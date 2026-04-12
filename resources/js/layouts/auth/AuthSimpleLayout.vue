<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh overflow-hidden"
        style="background: #020617;"
    >
        <!-- Grid background -->
        <div
            class="pointer-events-none absolute inset-0"
            style="
                background-image:
                    linear-gradient(rgba(59,130,246,0.07) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(59,130,246,0.07) 1px, transparent 1px);
                background-size: 60px 60px;
                mask-image: radial-gradient(ellipse 80% 80% at 50% 0%, rgba(0,0,0,0.8) 0%, transparent 100%);
            "
        />

        <!-- Glow top -->
        <div
            class="pointer-events-none absolute"
            style="
                width: 800px; height: 500px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 65%);
                filter: blur(60px);
                top: -250px; left: 50%; transform: translateX(-50%);
            "
        />

        <!-- Left: large wordmark -->
        <div class="relative hidden flex-1 flex-col justify-between p-12 lg:flex">
            <div>
                <div
                    class="font-display text-[7rem] font-extrabold leading-none tracking-tight"
                    style="
                        font-family: 'Syne', sans-serif;
                        background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(59,130,246,0.6) 100%);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                    "
                >
                    EVA
                </div>
                <p class="mt-4 text-base font-medium tracking-widest text-muted-foreground" style="letter-spacing: 0.2em;">
                    DEPARTAMENTO PESSOAL
                </p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground" style="max-width: 320px;">
                    Portal de gestão de pessoas e obrigações trabalhistas do Clube do Valor.
                </p>
            </div>
        </div>

        <!-- Right: login card -->
        <div class="relative flex w-full flex-col items-center justify-center p-8 lg:w-[440px] lg:border-l"
             style="border-color: rgba(59,130,246,0.1); background: rgba(2,6,23,0.6); backdrop-filter: blur(24px);">

            <!-- Mobile wordmark -->
            <div class="mb-8 text-center lg:hidden">
                <div
                    class="text-4xl font-extrabold"
                    style="
                        font-family: 'Syne', sans-serif;
                        background: linear-gradient(135deg, #ffffff 0%, #3b82f6 100%);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                    "
                >
                    EVA
                </div>
                <p class="mt-1 text-xs tracking-widest text-muted-foreground">DEPARTAMENTO PESSOAL</p>
            </div>

            <div class="w-full max-w-sm">
                <div v-if="title || description" class="mb-8">
                    <h1 v-if="title"
                        class="text-2xl font-bold text-white"
                        style="font-family: 'Syne', sans-serif;">
                        {{ title }}
                    </h1>
                    <p v-if="description" class="mt-2 text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
