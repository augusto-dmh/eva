<?php

namespace App\Ai\Tools;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\VacationBatchStatus;
use App\Models\Collaborator;
use App\Models\VacationBatch;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class VacationEligibilityTool implements Tool
{
    public function description(): string
    {
        return 'Consulta colaboradores elegíveis para férias que ainda não estão em lotes ativos. '
            .'Retorna nomes, datas de admissão, cargos e informações sobre lotes ativos de férias.';
    }

    public function handle(Request $request): string
    {
        $now = Carbon::now();
        $cltCutoff = $now->copy()->subMonths(12)->toDateString();
        $estCutoff = $now->copy()->subMonths(6)->toDateString();

        $alreadyScheduledIds = DB::table('vacation_batch_collaborators')
            ->join('vacation_batches', 'vacation_batch_collaborators.vacation_batch_id', '=', 'vacation_batches.id')
            ->whereIn('vacation_batches.status', ['calculado', 'em_revisao', 'confirmado', 'concluido'])
            ->where('vacation_batches.mes_referencia', '>=', $now->copy()->subMonths(12)->format('Y-m'))
            ->pluck('vacation_batch_collaborators.collaborator_id')
            ->toArray();

        $eligibleClt = Collaborator::where('status', CollaboratorStatus::Ativo)
            ->where('tipo_contrato', ContractType::Clt)
            ->where('data_admissao', '<=', $cltCutoff)
            ->whereNotIn('id', $alreadyScheduledIds)
            ->orderBy('data_admissao')
            ->get(['nome_completo', 'data_admissao', 'cargo', 'departamento']);

        $eligibleEst = Collaborator::where('status', CollaboratorStatus::Ativo)
            ->where('tipo_contrato', ContractType::Estagiario)
            ->where('data_admissao', '<=', $estCutoff)
            ->whereNotIn('id', $alreadyScheduledIds)
            ->orderBy('data_admissao')
            ->get(['nome_completo', 'data_admissao', 'cargo', 'departamento']);

        $activeBatches = VacationBatch::whereNotIn('status', [VacationBatchStatus::Concluido])
            ->get(['mes_referencia', 'tipo', 'status']);
        $batchInfo = $activeBatches->map(fn ($b) => "- {$b->mes_referencia} ({$b->tipo->label()}) — {$b->status->label()}")->implode("\n");

        $cltList = $eligibleClt->map(fn ($c) => "- {$c->nome_completo} | admissão: {$c->data_admissao} | {$c->cargo} | {$c->departamento}")->implode("\n");
        $estList = $eligibleEst->map(fn ($c) => "- {$c->nome_completo} | admissão: {$c->data_admissao} | {$c->departamento}")->implode("\n");

        return implode("\n\n", [
            "LOTES DE FÉRIAS ATIVOS:\n".($batchInfo ?: '(nenhum lote ativo)'),
            "CLT COM FÉRIAS PENDENTES DE AGENDAMENTO ({$eligibleClt->count()}):\n".($cltList ?: '(todos já agendados)'),
            "ESTAGIÁRIOS COM FÉRIAS PENDENTES ({$eligibleEst->count()}):\n".($estList ?: '(todos já agendados)'),
            "Regras: CLT = 12 meses de período aquisitivo, 30 dias + 1/3 constitucional. Estagiário = 6 meses, 15 dias.",
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
