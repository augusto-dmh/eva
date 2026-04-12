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
1. Compare os totais por entidade legal (Holding, Educação, Consultoria, Gestora, Corretora)
2. Identifique variações acima de 1% como relevantes
3. Calcule a variação em R$ e em percentual para cada linha divergente
4. Sugira causas prováveis para as divergências encontradas (admissões/desligamentos, reajustes, provisões, etc.)
5. Apresente um resumo executivo em português claro com prioridade para as maiores divergências

Formato da resposta:
- Título: "Análise de Discrepâncias — [mês/ano]"
- Seção: Divergências encontradas (tabela simplificada com entidade | folha | contabilidade | variação R$ | variação %)
- Seção: Causas prováveis
- Seção: Recomendações

Responda SEMPRE em português brasileiro. Seja objetivo e direto.
PROMPT;
    }

    public function analyze(array $payrollData, array $accountingData): string
    {
        $prompt = sprintf(
            "Analise as seguintes discrepâncias entre a folha de pagamento consolidada e os dados contábeis:\n\n"
            ."FOLHA DE PAGAMENTO (consolidado por entidade):\n%s\n\n"
            ."DADOS CONTÁBEIS (lançamentos do período):\n%s\n\n"
            .'Identifique divergências, calcule variações percentuais e sugira causas prováveis. '
            .'Apresente o resultado conforme o formato solicitado nas instruções.',
            json_encode($payrollData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            json_encode($accountingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $this->prompt(
            $prompt,
            provider: config('ai.default'),
            model: config('ai.default_model'),
        )->text;
    }
}
