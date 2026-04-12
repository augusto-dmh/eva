<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { PayrollCycle } from '@/types/payroll';

type Props = {
    cycle: PayrollCycle;
    allStatuses: { value: string; label: string }[];
};

const props = defineProps<Props>();

const currentIndex = computed(() =>
    props.allStatuses.findIndex((s) => s.value === props.cycle.status),
);

const nextStatus = computed(() => {
    const idx = currentIndex.value;

    if (idx < 0 || idx >= props.allStatuses.length - 1) {
        return null;
    }

    return props.allStatuses[idx + 1];
});

function advance() {
    if (!nextStatus.value) {
        return;
    }

    router.put(`/payroll-cycles/${props.cycle.id}`, {
        status: nextStatus.value.value,
    });
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-1 overflow-x-auto">
            <template
                v-for="(status, index) in allStatuses"
                :key="status.value"
            >
                <div
                    class="flex min-w-0 flex-shrink-0 items-center gap-1 rounded-full px-3 py-1 text-xs font-medium"
                    :class="{
                        'bg-blue-100 text-blue-800':
                            status.value === cycle.status,
                        'bg-green-100 text-green-700': index < currentIndex,
                        'bg-muted text-muted-foreground': index > currentIndex,
                    }"
                >
                    <span v-if="index < currentIndex" class="mr-1"
                        >&#10003;</span
                    >
                    {{ status.label }}
                </div>
                <div
                    v-if="index < allStatuses.length - 1"
                    class="h-px w-4 flex-shrink-0 bg-border"
                />
            </template>
        </div>

        <div v-if="nextStatus">
            <Button variant="default" size="sm" @click="advance">
                Avançar para: {{ nextStatus.label }}
            </Button>
        </div>
    </div>
</template>
