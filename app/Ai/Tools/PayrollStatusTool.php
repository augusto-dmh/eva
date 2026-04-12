<?php

namespace App\Ai\Tools;

use App\Enums\PjInvoiceStatus;
use App\Models\PayrollCycle;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class PayrollStatusTool implements Tool
{
    public function description(): string
    {
        return 'Consulta o status da folha de pagamento atual e dos últimos ciclos. '
            .'Retorna valores brutos, líquidos, deduções, status do ciclo e notas fiscais PJ pendentes.';
    }

    public function handle(Request $request): string
    {
        $cycles = PayrollCycle::orderByDesc('ano')->orderByDesc('mes')->limit(6)->get();

        if ($cycles->isEmpty()) {
            return 'Nenhum ciclo de folha de pagamento registrado no sistema.';
        }

        $lines = [];
        foreach ($cycles as $cycle) {
            $pendingPj = DB::table('pj_invoices')
                ->where('payroll_cycle_id', $cycle->id)
                ->where('status', PjInvoiceStatus::Pendente->value)
                ->count();

            $totalPj = DB::table('pj_invoices')
                ->where('payroll_cycle_id', $cycle->id)
                ->count();

            $lines[] = implode(' | ', [
                $cycle->mes_referencia,
                $cycle->status->label(),
                'Bruto: R$ '.number_format((float) $cycle->salarios_brutos, 2, ',', '.'),
                'Líquido: R$ '.number_format((float) $cycle->liquido, 2, ',', '.'),
                'PJ: R$ '.number_format((float) $cycle->pj, 2, ',', '.'),
                "NF pendentes: {$pendingPj}/{$totalPj}",
            ]);
        }

        return "ÚLTIMOS CICLOS DE FOLHA:\n".implode("\n", $lines);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
