# Eva — Portal de Departamento Pessoal

**Hackathon AI/Eficiência Clube do Valor | Abril 2026**

Eva é um portal de Departamento Pessoal que centraliza a gestão de pessoas e obrigações trabalhistas do Clube do Valor, substituindo planilhas dispersas e processos manuais por uma plataforma integrada com inteligência artificial.



https://github.com/user-attachments/assets/ca80a0c6-dba9-4319-b3d7-1ceb285a46be

---

## O Problema

O Departamento Pessoal do Clube do Valor opera com **5 entidades jurídicas** (Holding, Educação, Consultoria, Gestora, Corretora) e **280+ colaboradores** entre CLT, PJ, estagiários e sócios. Hoje, toda a operação de DP depende de:

- **Planilhas manuais** para controle de salários, férias, admissões e desligamentos
- **Cruzamento manual** de dados entre folha de pagamento, notas fiscais PJ, comissões e contabilidade
- **Cálculos repetitivos** de DSR sobre comissões, 13° proporcional, dissídio retroativo e rescisões
- **Comunicação por Slack/e-mail** para cobrar notas fiscais de PJ e alinhar com a contabilidade
- **Verificação manual** de prazos e obrigações trabalhistas (e-Social, férias vencidas, contribuição sindical)

### Custos reais da operação atual

| Métrica | Valor |
|---------|-------|
| Custo anual de um analista de DP dedicado | **R$ 69.600** (salário + encargos + benefícios) |
| Custo mensal do posto de trabalho com encargos (70% sobre base) | **R$ 5.800/mês** |
| Multa e-Social por infração (notificação atrasada) | **até R$ 6.351** |
| Multa sobre INSS recolhido incorretamente | **75% de acréscimo** |
| Exposição judicial (processo trabalhista) | **3x a 10x o valor da causa** |
| Tempo mensal em processamento de folha e encargos | **~4 dias** |
| Tempo em verificação manual de documentação | **~2h/dia** |

> *"O custo do modelo atual supera em 93% o salário nominal. A solução não é uma despesa adicional — é a substituição do nosso maior ativo, o tempo, por um investimento com retorno mensurável."*

### Riscos silenciosos

Processos manuais no DP não geram apenas retrabalho — geram **sanções automáticas**, **passivos judiciais** e **exposição à LGPD** com impacto financeiro que multiplica o custo original do erro. Uma fiscalização pode retroagir meses e multiplicar o passivo de forma indefinida.

### O que é feito manualmente hoje

O mapeamento completo das atividades de DP (`DP_Relação_atividades.csv`) revela dezenas de tarefas manuais distribuídas entre RH e Financeiro:

**Admissão** (por tipo de contrato):
- **CLT**: solicitar documentos → marcar exame admissional → compartilhar com contabilidade 2 dias antes → conferir salário e empresa → incluir na plataforma de assinatura
- **PJ**: redigir contrato manualmente alterando CNPJ, dados do prestador, função, remuneração, dados de contato → compartilhar PDF para assinatura → prazo ideal: 1ª semana
- **Estagiário**: solicitar processo junto ao FIJO → reunir documentação no Drive → conferir bolsa e empresa → compartilhar com contabilidade
- **Sócio**: após assinatura do contrato social, enviar dados (nome, PIS, nascimento) para contabilidade → solicitar inclusão em folha

**Após cada movimentação** (admissão, promoção, troca):
1. Inclusão na planilha de cargos e salários
2. Inclusão na planilha de Gestão de Vínculos
3. Classificação da empresa do colaborador
4. Inclusão de dados bancários
5. Inclusão no canal Slack do vínculo

**Benefícios**: inclusão manual na plataforma Flash para cada novo colaborador, ajuste de valores por tipo de contrato, suspensão/cancelamento em caso de afastamento ou desligamento.

### Impacto no setor financeiro

O DP não opera isolado — seus dados alimentam o **fechamento contábil mensal**, que hoje envolve:

- **Conciliação de saldo de clientes** entre 4+ gateways de pagamento (Rede, Hotmart, Pagarme, eNotas) com prazos de liquidação diferentes (D+2 a D+30)
- **Checklist de 40+ etapas** para reconciliar faturamento vs. recebimento, incluindo download de relatórios, limpeza de dados (remover "R$", substituir pontos por vírgulas), cruzamento manual entre abas
- **Faturamento PJ** manual: cobrança via Slack → espera de envio → conferência → lançamento na folha
- **Emissão de NFs** em múltiplas plataformas (eNotas, NFE.io, prefeituras de SP e POA) com regras diferentes por tipo de cliente (PF vs PJ), corretora (XP vs Genial) e cidade — incluindo lotes de até 500 NFs com tratamento manual de erros
- **Conciliação parcial** até dia 20 + **fechamento final** nos primeiros 2 dias úteis do mês seguinte, dependendo de extratos bancários, cartões, aplicações e folha de pagamento

> *Sem dados centralizados de DP, cada fechamento contábil exige retrabalho de cruzamento. Eva elimina essa dependência ao ser a fonte única de verdade para colaboradores, contratos, salários e movimentações.*

---

## A Solução

Eva centraliza toda a operação de DP em uma plataforma com **dados reais consultáveis em linguagem natural**, eliminando o cruzamento manual entre planilhas.

### Módulos implementados

| Módulo | O que resolve |
|--------|---------------|
| **Diretório de Colaboradores** | Ficha única por colaborador com dados pessoais, contratuais, bancários e benefícios Flash. Filtro por tipo de contrato, entidade jurídica e status. |
| **Folha de Pagamento** | Ciclo mensal com status machine (Aberto → Fechado). Consolidação por entidade. Portal de upload de NF para PJ. Gestão de comissões com DSR. |
| **Férias** | Motor de elegibilidade automático: CLT (12 meses) e estagiários (6 meses). Lotes de férias com simulação e confirmação. |
| **Admissão e Desligamento** | Checklists por tipo de contrato. Cálculo proporcional de rescisão com FGTS e aviso prévio. |
| **Dissídio Coletivo** | Simulação de reajuste em massa. Cálculo de retroativo (abono pecuniário sem INSS/FGTS). Aplicação com registro no histórico. |
| **13° Salário** | Simulador de duas parcelas com média de comissões. INSS e IRRF progressivos. |
| **PLR** | Distribuição proporcional por salário e tempo. IRRF exclusivo. Comitê de trabalhadores. |
| **Contribuição Sindical** | Registro de oposição por carta com AR. Controle de parcelamento. |
| **Histórico Profissional** | Log imutável de toda alteração: reajuste, promoção, troca de cargo, dissídio. Auditoria completa. |

---

## Assistente de DP — O Diferencial

O módulo de **Assistente de DP com IA** é o que transforma Eva de um sistema de gestão em uma ferramenta de produtividade real. Ele responde perguntas em linguagem natural sobre qualquer aspecto do Departamento Pessoal, consultando dados reais do sistema.

### Como funciona

```
Usuário digita uma pergunta
        ↓
DpAssistantAgent (Laravel AI SDK)
        ↓
LLM decide quais ferramentas chamar
        ↓
Ferramentas executam queries no banco
        ↓
LLM formula resposta em português com dados reais
        ↓
Resposta formatada em Markdown (tabelas, listas, cálculos)
```

### O que ele substitui

Hoje, para responder "Quem está elegível para férias agora?", o setor financeiro precisa:

1. Abrir a planilha de colaboradores
2. Filtrar por tipo de contrato (CLT)
3. Calcular a diferença entre a data de admissão e hoje para cada um
4. Verificar quem já tem 12+ meses
5. Cruzar com a planilha de férias para excluir quem já está agendado
6. Montar uma lista manualmente

**Com Eva:** digita a pergunta e recebe uma tabela com nomes, datas, cargos e departamentos em segundos.

### Ferramentas disponíveis

| Ferramenta | Dados consultados |
|-----------|-------------------|
| `VacationEligibilityTool` | CLTs e estagiários elegíveis, excluindo quem já está em lotes ativos |
| `PayrollStatusTool` | Últimos 6 ciclos de folha com bruto, líquido, PJ e NFs pendentes |
| `CollaboratorStatsTool` | Headcount por tipo/departamento, admissões e desligamentos recentes |
| `DissidioInfoTool` | Últimas rodadas de dissídio com percentuais e data-base |
| `AnnualObligationsTool` | Status de 13° salário e PLR com prazos e participantes |

### Conhecimento embutido

Além das ferramentas, o agente tem conhecimento das **regras trabalhistas brasileiras** para cálculos e explicações:

- Faixas progressivas de INSS 2025 (7,5% → 14%)
- IRRF sobre base (salário − INSS − dependentes)
- DSR sobre comissões: `(comissão bruta ÷ dias úteis) × domingos e feriados`
- 13° proporcional com regra do dia 15
- PLR com tabela IRRF exclusiva
- Dissídio retroativo como abono pecuniário
- Contribuição assistencial (2 dias em 4 parcelas)

### Base de conhecimento (few-shot)

O agente usa uma base de **exemplos bons e ruins** (`storage/ai/dp-assistant-examples.json`) para guiar a qualidade das respostas. Apenas os exemplos relevantes à pergunta são injetados via middleware — sem custo extra quando não há match.

### Funcionalidades do chat

- **Memória de conversa** — perguntas de acompanhamento funcionam ("quais desses são de tecnologia?")
- **Streaming** — respostas aparecem em tempo real (configurável via `AI_STREAMING_ENABLED`)
- **Download CSV** — dados estruturados podem ser baixados como CSV com nome baseado na pergunta
- **Histórico** — conversas salvas com navegação, favoritos, exclusão e título editável
- **Multi-provedor** — funciona com OpenAI, Anthropic, Groq, Gemini, DeepSeek, Zhipu (GLM) e outros

---

## Exemplos de uso

| Pergunta | O que o agente faz |
|---------|---------------------|
| "Quem está elegível para férias agora?" | Consulta banco → tabela com 34 CLTs elegíveis por nome, data, cargo, departamento |
| "Qual o status da folha atual?" | Últimos ciclos com valores brutos/líquidos e NFs PJ pendentes |
| "Quantos colaboradores CLT temos?" | Breakdown por tipo de contrato e departamento |
| "Como calcular o DSR de R$ 8.000 em comissões?" | Cálculo passo a passo com fórmula e valores |
| "Quais as faixas de INSS 2025?" | Tabela progressiva completa com parcelas a deduzir |
| "Simule o 13° de um colaborador admitido em maio com salário de R$ 5.000" | Cálculo proporcional com ambas parcelas e deduções |

---

## Stack técnica

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Vue 3 + Inertia.js v3 + TypeScript |
| UI | Tailwind CSS 4, shadcn/vue (reka-ui), Lucide icons |
| IA | Laravel AI SDK (`laravel/ai` v0.5.1) — provider-agnostic |
| Streaming | `@laravel/stream-vue` |
| Banco | SQLite (dev) / PostgreSQL 17 (prod) |
| Autenticação | Laravel Fortify (registro desabilitado — contas criadas por admin) |

---

## Configuração

```bash
# Clonar e instalar
git clone https://github.com/augusto-dmh/eva.git
cd eva
composer install
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Configurar IA (escolha seu provedor)
# No .env:
AI_DEFAULT_PROVIDER=openai        # openai | anthropic | groq | gemini | zhipu | ...
AI_DEFAULT_MODEL=gpt-4o-mini      # qualquer modelo do provedor
OPENAI_API_KEY=sk-...             # chave do provedor escolhido
AI_STREAMING_ENABLED=false        # true para streaming em tempo real

# Banco e seeders
php artisan migrate
php artisan db:seed

# Iniciar
npm run dev
php artisan serve
```

**Acesso:** `admin@clubedovalor.com.br` / `password`

---

## Impacto projetado

| Antes (manual) | Depois (Eva) |
|----------------|-------------|
| 4 dias/mês processando folha | Ciclo consolidado com status machine |
| 2h/dia verificando documentação | Checklists automáticos por tipo de contrato |
| Cruzamento manual entre 5+ planilhas | Pergunta em linguagem natural → resposta com dados |
| Risco de multa e-Social (até R$ 6.351/infração) | Dados centralizados e auditáveis |
| R$ 69.600/ano para um analista de DP | Custo de infraestrutura + API de IA |
| Sem histórico de alterações | Log imutável de toda mudança salarial/contratual |

---

## Documentação adicional

- [DP_ASSISTANT_AGENT.md](DP_ASSISTANT_AGENT.md) — arquitetura do agente, ferramentas, configuração e como estender
- [Eva_Project_Specification.md](Eva_Project_Specification.md) — especificação completa do projeto (domínio, enums, state machines, fases)

---

## Equipe

Desenvolvido para o **Hackathon AI Clube do Valor** (Abril 2026) com foco em eficiência operacional e redução de risco trabalhista.

> *"100% da capacidade produtiva do colaborador está consumida em rotinas manuais repetitivas. Não há espaço para análise, estratégia, ou verificação de conformidade."*

Eva transforma o Departamento Pessoal de um centro de custo operacional em uma **operação inteligente e auditável**.
