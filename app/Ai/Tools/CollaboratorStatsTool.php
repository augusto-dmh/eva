<?php

namespace App\Ai\Tools;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Models\Collaborator;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class CollaboratorStatsTool implements Tool
{
    public function description(): string
    {
        return 'Consulta estatísticas dos colaboradores: headcount por tipo de contrato, por departamento, '
            .'admissões recentes e colaboradores desligados.';
    }

    public function handle(Request $request): string
    {
        $now = Carbon::now();

        $active = Collaborator::where('status', CollaboratorStatus::Ativo)->get();
        $byType = $active->groupBy(fn ($c) => $c->tipo_contrato->label())->map->count();
        $byDept = $active->groupBy('departamento')->map->count()->sortDesc();

        $recentHires = Collaborator::where('status', CollaboratorStatus::Ativo)
            ->where('data_admissao', '>=', $now->copy()->subMonths(3)->toDateString())
            ->orderByDesc('data_admissao')
            ->get(['nome_completo', 'tipo_contrato', 'data_admissao', 'cargo', 'departamento']);

        $recentTerminations = Collaborator::where('status', CollaboratorStatus::Desligado)
            ->where('data_desligamento', '>=', $now->copy()->subMonths(3)->toDateString())
            ->orderByDesc('data_desligamento')
            ->get(['nome_completo', 'tipo_contrato', 'data_desligamento', 'cargo']);

        $typeLines = $byType->map(fn ($count, $type) => "- {$type}: {$count}")->implode("\n");
        $deptLines = $byDept->map(fn ($count, $dept) => "- {$dept}: {$count}")->implode("\n");

        $hireLines = $recentHires->isEmpty()
            ? '(nenhuma admissão nos últimos 3 meses)'
            : $recentHires->map(fn ($c) => "- {$c->nome_completo} | {$c->tipo_contrato->label()} | {$c->data_admissao} | {$c->cargo}")->implode("\n");

        $termLines = $recentTerminations->isEmpty()
            ? '(nenhum desligamento nos últimos 3 meses)'
            : $recentTerminations->map(fn ($c) => "- {$c->nome_completo} | {$c->tipo_contrato->label()} | {$c->data_desligamento}")->implode("\n");

        return implode("\n\n", [
            "HEADCOUNT ATIVO: {$active->count()} total\n{$typeLines}",
            "POR DEPARTAMENTO:\n{$deptLines}",
            "ADMISSÕES RECENTES (3 meses):\n{$hireLines}",
            "DESLIGAMENTOS RECENTES (3 meses):\n{$termLines}",
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
