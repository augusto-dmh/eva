<?php

namespace App\Ai\Tools;

use App\Models\DissidioRound;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class DissidioInfoTool implements Tool
{
    public function description(): string
    {
        return 'Consulta informações sobre dissídios coletivos: rodadas registradas, percentuais aplicados, '
            .'status de cada rodada e dados de simulação.';
    }

    public function handle(Request $request): string
    {
        $rounds = DissidioRound::orderByDesc('ano_referencia')->limit(5)->get();

        if ($rounds->isEmpty()) {
            return 'Nenhum dissídio registrado no sistema.';
        }

        $lines = $rounds->map(function ($r) {
            $entries = $r->entries()->count();
            $line = "- {$r->ano_referencia}: {$r->percentual}% | {$r->status->label()} | {$entries} colaboradores";
            if ($r->data_base) {
                $line .= " | data-base: {$r->data_base->format('d/m/Y')}";
            }

            return $line;
        })->implode("\n");

        return implode("\n\n", [
            "DISSÍDIOS REGISTRADOS:\n{$lines}",
            'Regras: Dissídio = reajuste coletivo anual negociado com o sindicato. '
            .'O diferencial retroativo (da data-base até a aplicação) é pago como abono pecuniário, sem incidência de INSS/FGTS.',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
