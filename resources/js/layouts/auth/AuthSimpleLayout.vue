<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative flex min-h-svh overflow-hidden bg-gray-50">
        <!-- Left: branding panel -->
        <div
            class="relative hidden flex-1 flex-col justify-between p-12 text-white lg:flex"
            style="background:#1e3a5f;"
        >
            <div>
                <div
                    class="text-[7rem] leading-none font-extrabold tracking-tight"
                    style="font-family:'Syne',sans-serif;color:rgba(255,255,255,0.95);"
                >
                    EVA
                </div>
                <p
                    class="mt-4 text-base font-medium tracking-widest"
                    style="letter-spacing:0.2em;color:rgba(255,255,255,0.6);"
                >
                    DEPARTAMENTO PESSOAL
                </p>
            </div>
            <div>
                <p class="text-sm" style="max-width:320px;color:rgba(255,255,255,0.5);">
                    Portal de gestão de pessoas e obrigações trabalhistas do
                    Clube do Valor.
                </p>
            </div>
        </div>

        <!-- Right: login card -->
        <div
            class="relative flex w-full flex-col items-center justify-center border-l border-border bg-white p-8 lg:w-[440px]"
        >
            <!-- Mobile wordmark -->
            <div class="mb-8 text-center lg:hidden">
                <div
                    class="text-4xl font-extrabold"
                    style="font-family:'Syne',sans-serif;color:#1e3a5f;"
                >
                    EVA
                </div>
                <p class="mt-1 text-xs tracking-widest text-muted-foreground">
                    DEPARTAMENTO PESSOAL
                </p>
            </div>

            <div class="w-full max-w-sm">
                <div v-if="title || description" class="mb-8">
                    <h1
                        v-if="title"
                        class="text-2xl font-bold text-foreground"
                        style="font-family:'Syne',sans-serif"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-2 text-sm text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
