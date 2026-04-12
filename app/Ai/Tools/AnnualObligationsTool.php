<?php

namespace App\Ai\Tools;

use App\Models\PlrRound;
use App\Models\ThirteenthSalaryRound;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class AnnualObligationsTool implements Tool
{
    public function description(): string
    {
        return 'Consulta obrigações anuais: 13° salário (rodadas, status, parcelas) e '
            .'PLR (rodadas, status do sindicato, valores distribuídos, comitê de trabalhadores).';
    }

    public function handle(Request $request): string
    {
        $year = Carbon::now()->year;

        // 13th salary
        $thirteenthRounds = ThirteenthSalaryRound::orderByDesc('ano_referencia')->limit(3)->get();
        $thirteenthLines = $thirteenthRounds->isEmpty()
            ? '(nenhuma rodada registrada)'
            : $thirteenthRounds->map(function ($r) {
                $entries = $r->entries()->count();

                return "- {$r->ano_referencia}: {$r->status->label()} | {$entries} colaboradores"
                    ." | 1ª parcela até {$r->primeira_parcela_data_limite}"
                    ." | 2ª parcela até {$r->segunda_parcela_data_limite}";
            })->implode("\n");

        // PLR
        $plrRounds = PlrRound::orderByDesc('ano_referencia')->limit(3)->get();
        $plrLines = $plrRounds->isEmpty()
            ? '(nenhuma rodada registrada)'
            : $plrRounds->map(function ($r) {
                $entries = $r->entries()->count();
                $valor = $r->valor_total_distribuido
                    ? 'R$ '.number_format((float) $r->valor_total_distribuido, 0, ',', '.')
                    : 'não definido';

                return "- PLR {$r->ano_referencia}: {$r->status->label()}"
                    ." | sindicato: {$r->status_sindicato->label()}"
                    ." | valor total: {$valor}"
                    ." | {$entries} participantes";
            })->implode("\n");

        return implode("\n\n", [
            "13° SALÁRIO:\n{$thirteenthLines}",
            "PLR (Participação nos Lucros):\n{$plrLines}",
            'Regras 13°: pro-rata por meses trabalhados no ano, 2 parcelas (nov/dez). INSS e IRRF progressivos.',
            'Regras PLR: distribuição proporcional a salário e meses trabalhados. IRRF com alíquota exclusiva de PLR (tabela separada da folha).',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
