# Assistente de DP — Arquitetura e Funcionamento

O Assistente de DP é o módulo de inteligência artificial da Eva. Ele responde perguntas em linguagem natural sobre Departamento Pessoal — férias, folha, dissídio, admissões, 13° salário, PLR — consultando os dados reais do banco de dados conforme necessário.

---

## Por que existe

Um analista de DP recebe dezenas de perguntas repetitivas todo mês:
*"Quem já está elegível para férias?"*, *"Qual o status da folha de março?"*, *"Quantos CLTs temos hoje?"*, *"Quando vence o prazo da segunda parcela do 13°?"*.

O Assistente de DP responde a todas essas perguntas instantaneamente, com dados reais do sistema, sem que o administrador precise navegar por múltiplas telas.

---

## Arquitetura

O agente usa a arquitetura **tool-calling** do [Laravel AI SDK](https://github.com/laravel/ai) (`laravel/ai` v0.5.1, construído sobre `prism-php/prism`).

```
Usuário → DpAssistantController → DpAssistantAgent
                                        │
                                 ┌──────▼──────┐
                                 │  LLM (via   │
                                 │  laravel/ai) │
                                 └──────┬──────┘
                                        │ decide quais ferramentas chamar
                          ┌─────────────┼─────────────────┐
                          ▼             ▼                  ▼
               VacationEligibility  PayrollStatus  CollaboratorStats
                    Tool               Tool              Tool
                          ▼             ▼
                   DissidioInfo   AnnualObligations
                      Tool            Tool
                          │
                          └──── resultados retornam ao LLM
                                        │
                                 resposta final em PT-BR
                                        │
                               DpAssistantController
                                        │
                                    Usuário
```

### Fluxo detalhado

1. O usuário digita uma pergunta no chat (`POST /dp-assistant/ask`).
2. `DpAssistantAgent::ask()` chama `$this->prompt($question, provider, model)`.
3. O SDK envia a pergunta + descrições de todas as ferramentas para o LLM.
4. O LLM decide **quais ferramentas** são relevantes para a pergunta e as invoca.
5. Cada ferramenta executa queries Eloquent no banco de dados e retorna texto estruturado.
6. O LLM recebe os dados retornados pelas ferramentas e formula a resposta final.
7. A resposta chega formatada em Markdown (negrito, tabelas, listas numeradas) e é renderizada no frontend com a biblioteca `marked`.

> **Não há dados injetados no prompt de sistema.** O contexto é carregado sob demanda, somente quando o LLM julga necessário. Isso mantém o custo de tokens baixo e a resposta focada.

---

## Ferramentas disponíveis

O agente implementa `HasTools` e expõe 5 ferramentas ao LLM. O SDK invoca automaticamente a ferramenta correta com base na descrição semântica de cada uma.

### 1. `VacationEligibilityTool`

**Quando o LLM invoca:** perguntas sobre férias — quem está elegível, quem precisa tirar férias, lotes ativos.

**Dados retornados:**
- Lista de colaboradores CLT com 12+ meses de período aquisitivo que ainda não estão em lotes ativos
- Lista de estagiários com 6+ meses sem lote ativo
- Lotes de férias em andamento (calculado, em revisão, confirmado)

**Regra de negócio aplicada:** exclui automaticamente colaboradores já presentes em lotes ativos dos últimos 12 meses, evitando duplicatas.

---

### 2. `PayrollStatusTool`

**Quando o LLM invoca:** perguntas sobre folha de pagamento — status do ciclo atual, valores, notas fiscais PJ pendentes.

**Dados retornados:**
- Últimos 6 ciclos de folha com mês de referência, status, total bruto, líquido, PJ
- Contagem de notas fiscais PJ pendentes por ciclo

---

### 3. `CollaboratorStatsTool`

**Quando o LLM invoca:** perguntas sobre headcount — quantos colaboradores, por tipo de contrato, por departamento, admissões e desligamentos recentes.

**Dados retornados:**
- Total de colaboradores ativos com breakdown por tipo de contrato (CLT, PJ, Estagiário, Sócio)
- Headcount por departamento (ordenado decrescente)
- Admissões dos últimos 3 meses (nome, tipo, cargo, departamento)
- Desligamentos dos últimos 3 meses

---

### 4. `DissidioInfoTool`

**Quando o LLM invoca:** perguntas sobre dissídio coletivo — percentuais históricos, status das rodadas, data-base.

**Dados retornados:**
- Últimas 5 rodadas de dissídio com ano de referência, percentual, status e data-base
- Regra do abono pecuniário retroativo (sem INSS/FGTS)

---

### 5. `AnnualObligationsTool`

**Quando o LLM invoca:** perguntas sobre 13° salário, PLR, obrigações anuais.

**Dados retornados:**
- Últimas 3 rodadas de 13° salário com status, número de colaboradores e datas-limite das parcelas
- Últimas 3 rodadas de PLR com status do sindicato, valor total distribuído e número de participantes

---

## Conhecimento embutido (sistema de instruções)

Além das ferramentas, o agente carrega um sistema de instruções com as **regras trabalhistas brasileiras** mais relevantes para DP. Esse conhecimento é usado para calcular e explicar sem necessidade de consultar ferramentas:

| Tópico | Conhecimento embutido |
|--------|----------------------|
| Férias CLT | 12 meses de período aquisitivo, 30 dias corridos + 1/3 constitucional |
| Férias Estagiário | 6 meses de período aquisitivo, 15 dias de recesso remunerado |
| 13° Salário | Pro-rata por meses trabalhados; admissão após dia 15 não conta o mês; 1ª parcela até 30/nov, 2ª até 20/dez |
| INSS 2025 | Faixas progressivas: 7,5% / 9% / 12% / 14% |
| IRRF | Tabela progressiva sobre base (salário − INSS − dependentes); isenção até R$ 2.824 |
| DSR sobre comissões | DSR = (comissão bruta / dias úteis) × domingos e feriados do mês |
| Dissídio | Reajuste coletivo anual; diferencial retroativo pago como abono pecuniário sem INSS/FGTS |
| PLR | IRRF exclusivo com tabela separada da folha (isenção até R$ 6.000) |
| Contribuição assistencial | 2 dias de salário em 4 parcelas; colaborador pode registrar oposição via carta com AR |

---

## Configuração do provedor de IA

O agente é completamente **agnóstico ao provedor**. A configuração é feita via variáveis de ambiente e `config/ai.php`.

### `.env`

```dotenv
AI_DEFAULT_PROVIDER=openai          # anthropic | openai | groq | gemini | deepseek | mistral | ollama | openrouter | xai | azure
AI_DEFAULT_MODEL=gpt-4o-mini        # qualquer modelo suportado pelo provedor
OPENAI_API_KEY=sk-...
# ANTHROPIC_API_KEY=sk-ant-...
# GROQ_API_KEY=gsk_...
```

### Provedores suportados

| Provedor | `AI_DEFAULT_PROVIDER` | Modelos comuns |
|---------|----------------------|----------------|
| OpenAI | `openai` | `gpt-4o`, `gpt-4o-mini`, `o3-mini` |
| Anthropic | `anthropic` | `claude-opus-4-6`, `claude-sonnet-4-6`, `claude-haiku-4-5-20251001` |
| Groq | `groq` | `llama-3.3-70b-versatile`, `mixtral-8x7b-32768` |
| Google Gemini | `gemini` | `gemini-2.0-flash`, `gemini-1.5-pro` |
| DeepSeek | `deepseek` | `deepseek-chat`, `deepseek-reasoner` |
| Mistral | `mistral` | `mistral-large-latest`, `mistral-small-latest` |
| Ollama (local) | `ollama` | `llama3.2`, `qwen2.5:7b` |
| OpenRouter | `openrouter` | qualquer modelo via gateway |
| xAI (Grok) | `xai` | `grok-3`, `grok-3-mini` |
| Azure OpenAI | `azure` | qualquer deployment Azure |

---

## Como adicionar uma nova ferramenta

1. Crie `app/Ai/Tools/MinhaNovaFerramenta.php` implementando `Laravel\Ai\Contracts\Tool`:

```php
<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class MinhaNovaFerramenta implements Tool
{
    public function description(): string
    {
        // Descrição semântica clara — o LLM usa isso para decidir quando chamar esta ferramenta
        return 'Consulta [o que esta ferramenta faz]. Retorna [o que ela retorna].';
    }

    public function handle(Request $request): string
    {
        // Execute queries Eloquent e retorne texto estruturado
        return "DADOS:\n...";
    }

    public function schema(JsonSchema $schema): array
    {
        // Parâmetros aceitos pela ferramenta (vazio = sem parâmetros)
        return [];
    }
}
```

2. Registre em `DpAssistantAgent::tools()`:

```php
public function tools(): iterable
{
    return [
        new VacationEligibilityTool,
        new PayrollStatusTool,
        new CollaboratorStatsTool,
        new DissidioInfoTool,
        new AnnualObligationsTool,
        new MinhaNovaFerramenta,   // ← adicione aqui
    ];
}
```

O LLM passa a considerar a nova ferramenta automaticamente em todas as conversas.

---

## Memória de conversa

O agente mantém contexto entre mensagens dentro de uma mesma conversa. Isso permite perguntas de acompanhamento como:

1. "Quem está elegível para férias agora?" → lista de nomes
2. "Quais desses são do departamento de tecnologia?" → o agente filtra a lista anterior

### Como funciona

O Laravel AI SDK persiste automaticamente as mensagens no banco de dados via `RemembersConversations` + `RememberConversation` middleware.

```
1ª mensagem → frontend envia { question, conversation_id: null }
            → backend chama forUser($user) → nova conversa criada
            → resposta inclui conversation_id

2ª mensagem → frontend envia { question, conversation_id: "abc-123" }
            → backend chama continue("abc-123", $user) → histórico carregado do DB
            → LLM recebe todas as mensagens anteriores + nova pergunta
```

**Limite:** 20 mensagens de contexto (configurável em `maxConversationMessages()`).

**Nova conversa:** O botão "Nova conversa" no chat reseta o `conversation_id` para `null`, iniciando uma conversa limpa sem recarregar a página.

**Persistência:** As conversas ficam nas tabelas `agent_conversations` e `agent_conversation_messages` (migration do SDK).

---

## Exemplos de perguntas suportadas

### Consultas ao Sistema Eva (invocam ferramentas)

| Pergunta | Ferramentas invocadas |
|---------|----------------------|
| "Quem está elegível para férias agora?" | `VacationEligibilityTool` |
| "Qual o status da folha de março?" | `PayrollStatusTool` |
| "Quantos CLTs temos hoje?" | `CollaboratorStatsTool` |
| "Quem foi admitido nos últimos 3 meses?" | `CollaboratorStatsTool` |
| "Qual o percentual do último dissídio?" | `DissidioInfoTool` |
| "Quando vence a segunda parcela do 13°?" | `AnnualObligationsTool` |

### Conhecimento Trabalhista (responde pelo conhecimento embutido)

| Pergunta | Ferramentas invocadas |
|---------|----------------------|
| "Como calculo o DSR sobre comissões?" | *nenhuma — conhecimento embutido* |
| "Quais são as faixas de INSS de 2025?" | *nenhuma — conhecimento embutido* |
| "Simule o 13° de um colaborador com salário R$ 5.000" | *nenhuma — calcula pelo conhecimento embutido* |

---

## Arquivos relevantes

| Arquivo | Descrição |
|---------|-----------|
| `app/Ai/Agents/DpAssistantAgent.php` | Agente principal — instruções + registro de ferramentas |
| `app/Ai/Tools/VacationEligibilityTool.php` | Ferramenta de elegibilidade de férias |
| `app/Ai/Tools/PayrollStatusTool.php` | Ferramenta de status da folha |
| `app/Ai/Tools/CollaboratorStatsTool.php` | Ferramenta de estatísticas de colaboradores |
| `app/Ai/Tools/DissidioInfoTool.php` | Ferramenta de informações de dissídio |
| `app/Ai/Tools/AnnualObligationsTool.php` | Ferramenta de obrigações anuais (13°, PLR) |
| `app/Http/Controllers/DpAssistantController.php` | Controller HTTP (página + endpoint AJAX) |
| `resources/js/pages/dp-assistant/Index.vue` | Interface de chat com renderização Markdown |
| `config/ai.php` | Configuração multi-provedor |
