<script setup lang="ts">
import { router } from '@inertiajs/vue3';

const props = defineProps<{ terminationId: number; flashCancelado: boolean }>();

function confirmFlashCancel() {
    router.put(`/termination-records/${props.terminationId}`, {
        flash_cancelado: true,
    });
}
</script>

<template>
    <div
        v-if="!flashCancelado"
        class="rounded-lg border border-red-500/50 bg-red-500/10 p-4"
    >
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="font-semibold text-red-400">
                    Atenção: Benefícios Flash não cancelados
                </p>
                <p class="text-sm text-red-300/80">
                    Os benefícios Flash deste colaborador ainda não foram
                    cancelados na plataforma Flash.
                </p>
            </div>
            <button
                class="shrink-0 rounded bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                @click="confirmFlashCancel"
            >
                Confirmar Cancelamento Flash
            </button>
        </div>
    </div>
    <div
        v-else
        class="rounded-lg border border-green-500/30 bg-green-500/10 p-3"
    >
        <p class="text-sm text-green-400">Flash cancelado confirmado.</p>
    </div>
</template>
