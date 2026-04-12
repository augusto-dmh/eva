import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { PayrollCycle } from '@/types/payroll';

export function usePayrollCycle(initialCycle: PayrollCycle) {
    const cycle = ref<PayrollCycle>(initialCycle);

    const canTransition = computed(() => ({
        toAguardandoNfPj: cycle.value.status === 'aberto',
        toAguardandoComissoes: cycle.value.status === 'aguardando_nf_pj',
        toEmRevisao: cycle.value.status === 'aguardando_comissoes',
        toConferidoContabilidade: cycle.value.status === 'em_revisao',
        toFechado: cycle.value.status === 'conferido_contabilidade',
    }));

    function transition(toStatus: string, cycleId: number) {
        router.put(`/payroll-cycles/${cycleId}`, { status: toStatus });
    }

    return { cycle, canTransition, transition };
}
