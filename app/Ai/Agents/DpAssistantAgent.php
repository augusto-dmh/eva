<?php

namespace App\Ai\Agents;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Models\Collaborator;
use App\Models\DissidioRound;
use App\Models\PayrollCycle;
use Carbon\Carbon;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class DpAssistantAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        $context = $this->buildContext();

        return <<<PROMPT
Você é o Assistente de DP da Eva, plataforma de Departamento Pessoal do Clube do Valor.
Responda perguntas sobre folha de pagamento, colaboradores, férias, dissídio e obrigações trabalhistas.
Seja objetivo, use português brasileiro, e baseie suas respostas nos dados fornecidos.

CONTEXTO ATUAL DO SISTEMA:
{$context}
PROMPT;
    }

    private function buildContext(): string
    {
        $cltCount = Collaborator::where('status', CollaboratorStatus::Ativo)
            ->where('tipo_contrato', ContractType::Clt)->count();
        $pjCount = Collaborator::where('status', CollaboratorStatus::Ativo)
            ->where('tipo_contrato', ContractType::Pj)->count();
        $estagiarioCount = Collaborator::where('status', CollaboratorStatus::Ativo)
            ->where('tipo_contrato', ContractType::Estagiario)->count();

        $latestCycle = PayrollCycle::latest()->first();
        $cycleInfo = $latestCycle
            ? "Ciclo atual: {$latestCycle->mes_referencia} (status: {$latestCycle->status->value})"
            : 'Nenhum ciclo de folha aberto.';

        $lastDissidio = DissidioRound::orderByDesc('ano_referencia')->first();
        $dissidioInfo = $lastDissidio
            ? "Último dissídio: {$lastDissidio->ano_referencia} ({$lastDissidio->percentual}% - {$lastDissidio->status->value})"
            : 'Nenhum dissídio registrado.';

        return implode("\n", [
            "- Colaboradores CLT ativos: {$cltCount}",
            "- Colaboradores PJ ativos: {$pjCount}",
            "- Estagiários ativos: {$estagiarioCount}",
            "- {$cycleInfo}",
            "- {$dissidioInfo}",
            '- Data atual: '.Carbon::now()->format('d/m/Y'),
        ]);
    }

    public function ask(string $question): string
    {
        return $this->prompt($question)->text;
    }
}
