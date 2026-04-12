<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class PayrollDiscrepancyAnalystAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'PROMPT'
Você é um especialista em análise de folha de pagamento da empresa Clube do Valor.
Sua função é comparar os dados consolidados da folha com os valores reportados pela contabilidade
e identificar discrepâncias significativas.

Ao analisar os dados:
1. Compare os totais por entidade legal (holding, educação, consultoria, gestora, corretora)
2. Identifique variações acima de 1% como relevantes
3. Sugira causas prováveis para as divergências encontradas
4. Apresente um resumo executivo em português claro

Responda SEMPRE em português brasileiro. Seja objetivo e direto.
PROMPT;
    }

    public function analyze(array $payrollData, array $accountingData): string
    {
        $prompt = sprintf(
            "Analise as seguintes discrepâncias entre a folha de pagamento consolidada e os dados contábeis:\n\n".
            "FOLHA DE PAGAMENTO:\n%s\n\n".
            "DADOS CONTÁBEIS:\n%s\n\n".
            'Identifique divergências, calcule variações percentuais e sugira causas prováveis.',
            json_encode($payrollData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            json_encode($accountingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $this->prompt($prompt)->text;
    }
}
