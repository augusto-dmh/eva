# Eva — Portal do Colaborador (Employee Portal for HR/DP Operations)

## Project Specification & AI Planning Guide

---

## 1. Project Overview

Eva is an internal web platform that centralizes all HR (Departamento Pessoal) and financial operations for Clube do Valor. It replaces a fragmented workflow of spreadsheets, email chains, and manual calculations with automated, auditable, role-controlled processes.

### Context

Clube do Valor operates across five legal entities (holding, educacao, consultoria, gestora, corretora). Employee master data — org chart, positions, personal data — already lives in **Nexus**, the company's internal platform. Eva is a **complementary system** that consumes and extends that data for all DP operations: payroll orchestration, benefits management, vacations, terminations, dissídio (collective wage adjustments), 13th salary, PLR (profit sharing), and annual union obligations.

### Problem Statement

- Payroll is currently assembled manually across multiple spreadsheets, requiring 2–3 days of reconciliation each month
- PJ invoice tracking is done via email thread, with no enforcement of submission deadlines
- Vacation eligibility is checked manually against a spreadsheet; missed accrual windows create legal exposure
- Dissídio adjustments are applied one-by-one, with retroactive corrections handled inconsistently
- There is no audit trail — who changed what, when, and why is unknowable from current state

### Roles

| Role | Access |
|------|--------|
| **Administrator** | Full access to all DP operations: payroll cycles, benefits, vacations, terminations, dissídio, PLR, checklists, union obligations, all collaborator profiles |
| **Collaborator** | Read-only view of their own profile and benefit breakdown; PJ collaborators can upload invoices for the active cycle |

### Delivery

Four phases, each producing 3 pull requests, from foundation to annual obligations.

### Business Value

| Metric | Value |
|--------|-------|
| Annual cost avoided | R$ 70,000/year (full DP headcount replacement) |
| e-Social compliance fines avoided | Up to R$ 6,000 per infraction |
| Severance miscalculation penalty | Up to 75% surcharge on incorrectly calculated amounts |
| Monthly payroll prep time reduction | Estimated 80% (from ~3 days to ~4 hours) |

---

## 2. Core Technology Stack

| Layer | Technology | Version / Notes |
|-------|-----------|-----------------|
| Backend | Laravel | 13.x |
| Language | PHP | 8.3 |
| Frontend Framework | Vue.js | 3.5 |
| SPA Bridge | Inertia.js | v3 |
| Frontend Language | TypeScript | 5.x |
| CSS Framework | Tailwind CSS | 4.x |
| UI Components | shadcn/vue (reka-ui) | Latest |
| Icons | Lucide Vue Next | Latest |
| Authentication | Laravel Fortify | Registration disabled — admin-created accounts only |
| Typed Routes | Laravel Wayfinder | Generates typed TypeScript route helpers |
| Testing | Pest | 4.x |
| PHP Formatter | Laravel Pint | Standard config |
| JS/TS Formatter | ESLint + Prettier | Shared config |
| Database (dev) | SQLite | Dev and hackathon demo |
| Database (prod) | PostgreSQL | 17.x |
| Queue Driver (dev) | Database | Laravel database queue |
| Queue Driver (prod) | Redis | With Horizon |
| AI SDK | `laravel/ai` v0.5.1 | Package: `composer require laravel/ai`; built on `prism-php/prism`; provider-agnostic: Anthropic, OpenAI, Gemini |
| Build Tool | Vite | 8.x |
| File Storage | Laravel Storage | Local (dev), S3-compatible (prod) |

---

## 3. Non-Functional Constraints

### 3.1 Security

- **Role-based access control** enforced via Laravel Gates and Policies on every controller action; no action is unauthenticated
- **Admin gate** registered in `AuthServiceProvider`: `Gate::define('admin', fn($user) => $user->role === UserRole::Admin)`
- **PJ invoice uploads**: file validation enforces `mimes:pdf` and `max:10240` (10MB); stored outside `public/` with signed URL access only
- **CPF validation** on collaborator creation using modulo-11 algorithm; unique constraint enforced at DB level
- **No self-registration**: Fortify's `register` feature disabled; only admins can create user accounts
- **Sensitive fields** (banking info, CPF, PIS) never exposed in API index responses; only returned in detail/edit views for admins

### 3.2 Performance

- Collaborator index page must load in **< 500ms** for up to 200 records (use `select()` projection, avoid N+1 with eager loading)
- Dissídio simulation must complete in **< 5 seconds** for 100 CLT employees (run via queued job with progress event)
- 13th salary calculation must complete in **< 3 seconds** (synchronous for < 50 employees, queued otherwise)
- All list pages use server-side pagination (15 records per page default)

### 3.3 Reliability

- **All monetary values** use `decimal(12,2)` column type — never PHP `float` or JavaScript `number` for money; use string-based arithmetic or `bcmath` in PHP
- **Professional history entries are immutable**: the `ProfessionalHistoryEntry` model overrides `save()`, `update()`, and `delete()` to throw `ImmutableModelException`; no `updated_at` column exists
- **Payroll cycle status machine** prevents backwards transitions; terminal state `Fechado` cannot be reopened; transitions validated in `PayrollCycleService::transition()`
- All database operations that span multiple tables are wrapped in `DB::transaction()`

### 3.4 Observability

- **Admin action logging**: every write action (create, update, delete, status transition) records an entry in Laravel's standard log channel with `user_id`, `action`, `model`, `model_id`, and timestamp; structured log format
- **PayrollCycle transitions** additionally stored in a `payroll_cycle_events` table (cycle_id, from_status, to_status, triggered_by, created_at)
- **Queue job failures** tracked via Laravel's built-in `failed_jobs` table; `failed_jobs` table seeded in migrations
- **Slack simulation mode**: when `SLACK_SIMULATE=true`, all Slack API calls are replaced with `Log::channel('slack')->info(...)` entries

---

## 4. Domain Model

### 4.1 Entity Relationship Overview

```
users
  └── collaborators (1:1 optional, via collaborator_id)

legal_entities
  ├── collaborators (1:N via legal_entity_id)
  ├── payroll_entries (1:N via legal_entity_id)
  ├── plr_committee_members (1:N via legal_entity_id)
  └── syndicate_bindings (N:M → syndicates)

collaborators
  ├── flash_benefit_profiles (1:1)
  ├── professional_history_entries (1:N, IMMUTABLE)
  ├── admission_checklists (1:1)
  ├── payroll_entries (1:N via collaborator_id)
  ├── pj_invoices (1:N)
  ├── vacation_batch_collaborators (1:N pivot)
  ├── termination_records (1:1 optional)
  ├── dissidio_entries (1:N)
  ├── thirteenth_salary_entries (1:N)
  ├── plr_entries (1:N)
  ├── plr_committee_members (1:N)
  └── assistive_convention_records (1:N by year)

payroll_cycles
  ├── payroll_entries (1:N)
  └── pj_invoices (1:N via payroll_cycle_id)

payroll_entries
  └── pj_invoices (1:1 optional)

vacation_batches
  └── vacation_batch_collaborators (1:N pivot)

dissidio_rounds
  ├── dissidio_entries (1:N)
  └── professional_history_entries (1:N via dissidio_round_id)

thirteenth_salary_rounds
  └── thirteenth_salary_entries (1:N)

plr_rounds
  ├── plr_entries (1:N)
  └── plr_committee_members (1:N)

admission_checklists
  └── admission_checklist_items (1:N)

syndicates
  └── syndicate_bindings (N:M → legal_entities)
```

Total tables: 22 domain tables + `users`, `jobs`, `failed_jobs`, `cache`, `sessions` (Laravel system tables)

---

### 4.2 Entity Definitions

#### `users`

Extension of the default Laravel users table.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | Auto-increment |
| name | string | Display name |
| email | string unique | Login credential |
| password | string | Hashed with bcrypt |
| role | string | Default `'collaborator'`; cast to `UserRole` enum |
| collaborator_id | foreignId nullable | Links to `collaborators.id`; null for admin-only accounts |
| email_verified_at | timestamp nullable | Standard Fortify field |
| remember_token | string nullable | Standard |
| created_at / updated_at | timestamps | Standard |

**Enum**: `UserRole { Admin = 'admin', Collaborator = 'collaborator' }`

---

#### `legal_entities`

Represents each of Clube do Valor's five corporate entities.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| nome | string | Full legal name |
| apelido | string | Short name: `holding`, `educacao`, `consultoria`, `gestora`, `corretora` |
| cnpj | string(18) unique | Formatted: `XX.XXX.XXX/XXXX-XX` |
| sindicato_patronal | string nullable | Name of employer union |
| sindicato_trabalhadores | string nullable | Name of workers' union |
| ativo | boolean | Default `true` |
| created_at / updated_at | timestamps | |

---

#### `collaborators`

The central employee/contractor record.

**Personal Information**

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| nome_completo | string | Full name |
| cpf | string(14) unique | Formatted: `XXX.XXX.XXX-XX`; validated via modulo-11 |
| email_corporativo | string unique | Work email |
| email_pessoal | string nullable | Personal email |
| data_nascimento | date | |
| telefone | string nullable | |

**Employment Information**

| Column | Type | Notes |
|--------|------|-------|
| tipo_contrato | string | Cast to `ContractType` enum: `clt`, `pj`, `estagiario`, `socio` |
| legal_entity_id | foreignId | References `legal_entities.id` |
| departamento | string | Department name |
| cargo | string | Job title |
| nivel | string nullable | Level (Junior, Pleno, Senior, etc.) |
| trilha_carreira | string nullable | Career track |
| lider_direto | string nullable | Direct manager name (denormalized) |
| status | string | Cast to `CollaboratorStatus` enum |
| data_admissao | date | |
| data_desligamento | date nullable | Filled on termination |

**Flash Benefits Snapshot** _(denormalized from `flash_benefit_profiles` for fast display)_

| Column | Type | Notes |
|--------|------|-------|
| flash_numero_cartao | string nullable | Flash card number |
| flash_vale_alimentacao | decimal(10,2) nullable | Food allowance |
| flash_vale_refeicao | decimal(10,2) nullable | Meal allowance |
| flash_vale_transporte | decimal(10,2) nullable | Transportation |
| flash_saude | decimal(10,2) nullable | Health supplement |
| flash_cultura | decimal(10,2) nullable | Culture benefit |
| flash_educacao | decimal(10,2) nullable | Education benefit |
| flash_home_office | decimal(10,2) nullable | Home office subsidy |
| flash_total | decimal(10,2) nullable | Computed sum |

**Compensation**

| Column | Type | Notes |
|--------|------|-------|
| salario_base | decimal(12,2) | Pro-labore for PJ/Socio; CLT salary |
| tipo_comissao | string | Cast to `CommissionType` enum: `none`, `closer`, `advisor` |
| minimo_garantido | decimal(12,2) nullable | Guaranteed minimum for Advisors in ramp-up |
| elegivel_comissao | boolean | Default `false` |
| desconto_petlove | decimal(10,2) nullable | Pet insurance payroll deduction |

**Banking**

| Column | Type | Notes |
|--------|------|-------|
| banco | string nullable | Bank name or code |
| agencia | string nullable | Branch number |
| conta | string nullable | Account number |
| chave_pix | string nullable | PIX key (CPF, email, phone, or random) |

**Integration**

| Column | Type | Notes |
|--------|------|-------|
| nexus_employee_id | bigint nullable | ID in the Nexus platform |
| pis | string nullable | PIS/NIT number (for CLT) |
| slack_user_id | string nullable | Slack member ID for DMs |

**Meta**

| Column | Type | Notes |
|--------|------|-------|
| created_at / updated_at | timestamps | |
| deleted_at | timestamp nullable | Soft deletes |

**Enums**:
- `ContractType { Clt = 'clt', Pj = 'pj', Estagiario = 'estagiario', Socio = 'socio' }`
- `CollaboratorStatus { Ativo = 'ativo', Afastado = 'afastado', Desligado = 'desligado' }`
- `CommissionType { None = 'none', Closer = 'closer', Advisor = 'advisor' }`

---

#### `flash_benefit_profiles`

Detailed Flash Benefícios configuration for each collaborator (1:1).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| collaborator_id | foreignId unique | References `collaborators.id` |
| flash_empresa_vinculo | string nullable | Flash employer entity code |
| valor_fixo | decimal(10,2) | Fixed benefit amount |
| valor_variavel | decimal(10,2) | Variable benefit amount |
| desconto_petlove | decimal(10,2) | Pet insurance discount applied via Flash |
| outros_descontos | decimal(10,2) | Other deductions |
| descricao_outros | string nullable | Description of other deductions |
| data_inclusao_flash | date nullable | Enrollment date on Flash platform |
| data_exclusao_flash | date nullable | Exclusion date from Flash platform |
| status_flash | string | Cast to `FlashStatus` enum: `pendente`, `ativo`, `suspenso`, `cancelado` |
| observacoes | text nullable | Admin notes |
| created_at / updated_at | timestamps | |

---

#### `professional_history_entries`

Immutable audit log of every compensation or employment change.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| collaborator_id | foreignId | References `collaborators.id` |
| tipo_evento | string | Cast to `ProfessionalEventType` enum |
| data_efetivacao | date | Effective date of the change |
| campo_alterado | string | Field that changed (e.g., `salario_base`, `cargo`) |
| valor_anterior | string nullable | Previous value serialized as string |
| valor_novo | string nullable | New value serialized as string |
| motivo | string | `AdjustmentReason` enum value or free text |
| dissidio_round_id | foreignId nullable | If generated by a dissídio round |
| observacoes | text nullable | |
| registrado_por_id | foreignId | References `users.id` |
| created_at | timestamp | **No `updated_at`** — immutable record |

**Enums**:
- `ProfessionalEventType { Admissao, Promocao, AjusteSalarial, AlteracaoTipoContrato, Desligamento, Dissidio, AlteracaoCargo, AlteracaoDepartamento }`
- `AdjustmentReason { Merito, Dissidio, Promocao, Reajuste, Correcao, Politica }`

**Immutability enforcement** in `ProfessionalHistoryEntry` model:
```php
public function save(array $options = []): bool
{
    if ($this->exists) {
        throw new ImmutableModelException('ProfessionalHistoryEntry records cannot be modified.');
    }
    return parent::save($options);
}

protected static function booted(): void
{
    static::updating(fn() => throw new ImmutableModelException('...'));
    static::deleting(fn() => throw new ImmutableModelException('...'));
}
```

---

#### `admission_checklists`

One checklist per collaborator, created upon admission.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| collaborator_id | foreignId unique | References `collaborators.id` |
| tipo_contrato | string | Denormalized from collaborator at creation time |
| status | string | Cast to `ChecklistStatus` enum |
| data_limite | date | Deadline for checklist completion |
| completado_em | timestamp nullable | When all items confirmed |
| completado_por_id | foreignId nullable | Admin who confirmed completion |
| observacoes | text nullable | |
| created_at / updated_at | timestamps | |

**Enum**: `ChecklistStatus { Pendente = 'pendente', EmAndamento = 'em_andamento', Completo = 'completo', Bloqueado = 'bloqueado' }`

---

#### `admission_checklist_items`

Individual items within an admission checklist.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| admission_checklist_id | foreignId | References `admission_checklists.id` |
| descricao | string | Item description |
| obrigatorio | boolean | Default `true` |
| confirmado | boolean | Default `false` |
| confirmado_em | timestamp nullable | |
| confirmado_por_id | foreignId nullable | References `users.id` |
| documento_path | string nullable | Uploaded supporting document path |
| observacoes | text nullable | |
| ordem | integer | Display order |
| created_at / updated_at | timestamps | |

---

#### `payroll_cycles`

One record per calendar month — the container for all payroll operations.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| mes_referencia | string(7) | Format: `YYYY-MM` (e.g., `2025-01`) |
| ano | smallint unsigned | Year component |
| mes | tinyint unsigned | Month component (1–12) |
| status | string | Cast to `PayrollCycleStatus` enum |
| data_abertura | timestamp | When cycle was opened |
| data_fechamento | timestamp nullable | When cycle was closed |
| data_pagamento_folha | date nullable | Scheduled payroll payment date |
| data_pagamento_comissao | date nullable | Scheduled commission payment date |
| salarios_brutos | decimal(14,2) | Aggregated total |
| comissoes | decimal(14,2) | |
| deducoes | decimal(14,2) | |
| liquido | decimal(14,2) | |
| pj | decimal(14,2) | Total PJ invoices |
| observacoes | text nullable | |
| fechado_por_id | foreignId nullable | References `users.id` |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(ano, mes)`

**Enum**: `PayrollCycleStatus { Aberto, AguardandoNfPj, AguardandoComissoes, EmRevisao, ConferidoContabilidade, Fechado }`

---

#### `payroll_entries`

One record per collaborator per payroll cycle.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| payroll_cycle_id | foreignId | References `payroll_cycles.id` |
| collaborator_id | foreignId | References `collaborators.id` |
| tipo_contrato | string | Denormalized at creation time |
| legal_entity_id | foreignId | References `legal_entities.id` |
| salario_bruto | decimal(12,2) | |
| salario_proporcional | boolean | `true` if partial month |
| dias_trabalhados | tinyint unsigned nullable | |
| dias_uteis_mes | tinyint unsigned nullable | |
| valor_comissao_bruta | decimal(12,2) | Pre-DSR commission |
| valor_dsr | decimal(12,2) | Calculated DSR addition |
| valor_comissao_total | decimal(12,2) | Commission + DSR |
| desconto_inss | decimal(12,2) | |
| desconto_irrf | decimal(12,2) | |
| desconto_contribuicao_assistencial | decimal(12,2) | Union assistive contribution deduction |
| desconto_petlove | decimal(10,2) | |
| desconto_outros | decimal(10,2) | |
| descricao_desconto_outros | string nullable | |
| bonificacoes | decimal(12,2) | |
| descricao_bonificacoes | string nullable | |
| valor_liquido | decimal(12,2) | Net pay |
| valor_fgts | decimal(12,2) | FGTS (8% of gross, employer cost) |
| valor_inss_patronal | decimal(12,2) | Employer INSS portion |
| valor_nota_fiscal_pj | decimal(12,2) nullable | Only for PJ collaborators |
| status | string | Cast to `PayrollEntryStatus` enum |
| observacoes | text nullable | |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(payroll_cycle_id, collaborator_id)`

**Enum**: `PayrollEntryStatus { Pendente, Preenchido, Revisado, Aprovado }`

---

#### `pj_invoices`

Invoice submitted by a PJ collaborator for a given month.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| payroll_entry_id | foreignId nullable | Linked after review |
| collaborator_id | foreignId | References `collaborators.id` |
| payroll_cycle_id | foreignId | References `payroll_cycles.id` |
| numero_nota | string | Invoice number from the NF-e |
| valor | decimal(12,2) | Invoice amount |
| arquivo_path | string | Storage path (outside public) |
| arquivo_nome_original | string | Original filename |
| data_upload | timestamp | |
| data_emissao | date | Issue date on the invoice |
| cnpj_emissor | string(18) | Collaborator's company CNPJ |
| cnpj_destinatario | string(18) | Clube do Valor entity CNPJ |
| status | string | Cast to `PjInvoiceStatus` enum |
| observacoes | text nullable | Admin review notes |
| uploaded_by_id | foreignId | References `users.id` |
| revisado_por_id | foreignId nullable | Admin who reviewed |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(payroll_cycle_id, collaborator_id)`

**Enum**: `PjInvoiceStatus { Pendente, Recebida, EmRevisao, Aprovada, Rejeitada }`

---

#### `vacation_batches`

A batch groups all eligible collaborators for a given vacation cycle.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| mes_referencia | string(7) | Format `YYYY-MM` |
| tipo | string | Cast to `VacationBatchType` enum: `clt`, `estagiario` |
| periodo_aquisitivo_minimo_meses | tinyint unsigned | 12 for CLT, 6 for Estagiario |
| dias_ferias | tinyint unsigned | 30 for CLT, 15 for Estagiario |
| status | string | Cast to `VacationBatchStatus` enum |
| data_abertura | timestamp nullable | |
| data_fechamento | timestamp nullable | |
| observacoes | text nullable | |
| criado_por_id | foreignId | References `users.id` |
| created_at / updated_at | timestamps | |

**Enums**:
- `VacationBatchType { Clt = 'clt', Estagiario = 'estagiario' }`
- `VacationBatchStatus { Rascunho, Calculado, EmRevisao, Confirmado, Concluido }`

---

#### `vacation_batch_collaborators`

Pivot table linking collaborators to vacation batches with all computed values.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| vacation_batch_id | foreignId | References `vacation_batches.id` |
| collaborator_id | foreignId | References `collaborators.id` |
| data_admissao | date | Denormalized at computation time |
| periodo_aquisitivo_inicio | date | Start of accrual period |
| periodo_aquisitivo_fim | date | End of accrual period |
| meses_acumulados | tinyint unsigned | Months in current accrual period |
| elegivel | boolean | Computed eligibility flag |
| data_inicio_ferias | date nullable | Scheduled vacation start |
| data_fim_ferias | date nullable | Scheduled vacation end |
| valor_ferias | decimal(12,2) nullable | Gross vacation pay |
| valor_terco_constitucional | decimal(12,2) nullable | Constitutional 1/3 addition |
| status | string | Cast to `VacationCollaboratorStatus` enum |
| aviso_enviado | boolean | Default `false` |
| aviso_assinado | boolean | Default `false` |
| observacoes | text nullable | |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(vacation_batch_id, collaborator_id)`

**Enum**: `VacationCollaboratorStatus { Pendente, Agendado, Aviso_Enviado, Confirmado, Concluido }`

---

#### `termination_records`

All data for a collaborator's termination/rescisão.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| collaborator_id | foreignId unique | One termination record per collaborator |
| tipo_desligamento | string | Cast to `TerminationType` enum |
| data_comunicacao | date | When termination was communicated |
| data_efetivacao | date | Legal termination effective date |
| motivo | text nullable | |
| salario_proporcional_dias | tinyint unsigned | Days worked in final partial month |
| salario_proporcional_valor | decimal(12,2) | |
| ferias_proporcionais_valor | decimal(12,2) | Accrued vacation payout |
| terco_ferias_proporcionais | decimal(12,2) | 1/3 addition on proportional vacation |
| decimo_terceiro_proporcional | decimal(12,2) | Proportional 13th salary |
| multa_fgts | decimal(12,2) | FGTS fine: 40% of balance (involuntary) |
| aviso_previo_valor | decimal(12,2) | Notice period pay |
| indenizacao_rescisoria | decimal(12,2) | Additional contractual severance |
| valor_total_rescisao | decimal(12,2) | Grand total |
| ajuste_flash_valor | decimal(10,2) | Prorated Flash benefit credit/debit |
| flash_cancelado | boolean | Default `false` — alert shown until true |
| exame_demissional_agendado | boolean | Default `false` |
| exame_demissional_data | date nullable | |
| previa_contabilidade_solicitada | boolean | Default `false` |
| previa_contabilidade_conferida | boolean | Default `false` |
| documentos_enviados_rh | boolean | Default `false` |
| status | string | Cast to `TerminationStatus` enum |
| processado_por_id | foreignId | References `users.id` |
| created_at / updated_at | timestamps | |

**Enums**:
- `TerminationType { PedidoDemissao, DispensaSemJustaCausa, DispensaComJustaCausa, MutuoAcordo, TerminoContrato }`
- `TerminationStatus { Iniciado, SimulacaoRealizada, PreviaSolicitada, PreviaConferida, DocumentacaoEnviada, Concluido }`

---

#### `dissidio_rounds`

One record per annual collective wage adjustment round.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| ano_referencia | smallint unsigned | Year of the dissídio |
| data_base | date | Union data-base date (usually Feb 1st) |
| data_publicacao | date nullable | When agreement was published |
| percentual | decimal(6,4) | Adjustment percentage (e.g., `0.0550` = 5.50%) |
| aplica_estagiarios | boolean | Default `false` |
| status | string | Cast to `DissidioRoundStatus` enum |
| observacoes | text nullable | |
| criado_por_id | foreignId | References `users.id` |
| aplicado_por_id | foreignId nullable | Admin who applied the round |
| aplicado_em | timestamp nullable | |
| created_at / updated_at | timestamps | |

**Enum**: `DissidioRoundStatus { Rascunho, Simulado, AguardandoAprovacao, Aplicado, RelatorioGerado }`

---

#### `dissidio_entries`

Per-collaborator result of a dissídio round.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| dissidio_round_id | foreignId | References `dissidio_rounds.id` |
| collaborator_id | foreignId | References `collaborators.id` |
| salario_anterior | decimal(12,2) | Salary before adjustment |
| percentual_aplicado | decimal(6,4) | Actual percentage applied |
| salario_novo | decimal(12,2) | Salary after adjustment |
| diferenca_retroativa | decimal(12,2) | Retroactive differential |
| meses_retroativos | tinyint unsigned | Months of retroactive difference |
| status | string | `simulado` or `aplicado` |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(dissidio_round_id, collaborator_id)`

---

#### `thirteenth_salary_rounds`

Annual 13th salary calculation container.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| ano_referencia | smallint unsigned unique | Year (e.g., 2025) |
| status | string | Cast to `ThirteenthRoundStatus` enum |
| primeira_parcela_data_limite | date | Default November 30 |
| segunda_parcela_data_limite | date | Default December 20 |
| observacoes | text nullable | |
| criado_por_id | foreignId | References `users.id` |
| created_at / updated_at | timestamps | |

**Enum**: `ThirteenthRoundStatus { Aberto, PrimeiraParcelaSimulada, PrimeiraParcelaPaga, SegundaParcelaSimulada, SegundaParcelaPaga, Concluido }`

---

#### `thirteenth_salary_entries`

Per-collaborator 13th salary calculation.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| thirteenth_salary_round_id | foreignId | References `thirteenth_salary_rounds.id` |
| collaborator_id | foreignId | References `collaborators.id` |
| meses_trabalhados | tinyint unsigned | Months worked in the year (1–12) |
| salario_base | decimal(12,2) | Salary at calculation time |
| media_comissoes | decimal(12,2) | Average monthly commissions for the year |
| base_calculo | decimal(12,2) | `salario_base + media_comissoes` |
| valor_integral | decimal(12,2) | Full year value before proportionality |
| primeira_parcela_valor | decimal(12,2) | 50% of `valor_integral`, no deductions |
| segunda_parcela_valor | decimal(12,2) | Remaining after INSS and IRRF |
| desconto_inss | decimal(12,2) | INSS on second installment |
| desconto_irrf | decimal(12,2) | IRRF on second installment |
| primeira_parcela_status | string | Cast to `InstallmentStatus` enum |
| segunda_parcela_status | string | Cast to `InstallmentStatus` enum |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(thirteenth_salary_round_id, collaborator_id)`

**Enum**: `InstallmentStatus { Pendente, Simulado, Pago }`

---

#### `plr_rounds`

Annual PLR (Profit Sharing) distribution container.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| ano_referencia | smallint unsigned unique | |
| documento_politica_path | string nullable | Uploaded PLR policy document |
| documento_politica_revisado | boolean | Default `false` |
| status_sindicato | string | Cast to `PlrSyndicateStatus` enum |
| data_aprovacao_sindicato | date nullable | |
| valor_total_distribuido | decimal(14,2) nullable | Total distributed amount |
| status | string | Cast to `PlrRoundStatus` enum |
| observacoes | text nullable | |
| criado_por_id | foreignId | References `users.id` |
| created_at / updated_at | timestamps | |

**Enums**:
- `PlrSyndicateStatus { NaoIniciado, Enviado, Aprovado, Rejeitado }`
- `PlrRoundStatus { Rascunho, DocumentoEnviado, ComiteCriado, AguardandoSindicato, Aprovado, Simulado, Pago }`

---

#### `plr_entries`

Per-collaborator PLR calculation.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| plr_round_id | foreignId | References `plr_rounds.id` |
| collaborator_id | foreignId | References `collaborators.id` |
| media_salarios_ano | decimal(12,2) | Average salary across the year |
| meses_trabalhados | tinyint unsigned | Months worked |
| valor_simulado | decimal(12,2) | Simulation result |
| valor_pago | decimal(12,2) nullable | Actual paid amount |
| desconto_irrf | decimal(12,2) | IRRF on PLR (special aliquot table) |
| status | string | Cast to `PlrEntryStatus` enum |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(plr_round_id, collaborator_id)`

**Enum**: `PlrEntryStatus { Simulado, Aprovado, Pago }`

---

#### `plr_committee_members`

Workers' Committee required by Brazilian law for PLR programs.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| plr_round_id | foreignId | References `plr_rounds.id` |
| collaborator_id | foreignId | References `collaborators.id` |
| legal_entity_id | foreignId | References `legal_entities.id` |
| papel | string | `membro` or `presidente` |
| ativo | boolean | Default `true` |
| created_at / updated_at | timestamps | |

---

#### `assistive_convention_records`

Annual union assistive contribution (Contribuição Assistencial) tracking.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| collaborator_id | foreignId | References `collaborators.id` |
| ano_referencia | smallint unsigned | |
| fez_oposicao | boolean | True if collaborator sent opposition letter |
| data_oposicao | date nullable | Date opposition letter was sent |
| comprovante_ar_path | string nullable | Proof of delivery (AR) document path |
| confirmado_sindicato | boolean | Default `false` |
| parcelas_descontadas | tinyint unsigned | Default 0 (out of 4) |
| total_parcelas | tinyint unsigned | Default 4 |
| valor_parcela | decimal(10,2) nullable | Amount per installment |
| observacoes | text nullable | |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(collaborator_id, ano_referencia)`

---

#### `syndicates`

Union registry for both employer and workers' unions.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| nome | string | Full union name |
| tipo | string | Cast to `SyndicateType` enum: `patronal`, `trabalhadores` |
| uf | string(2) | State abbreviation |
| created_at / updated_at | timestamps | |

**Enum**: `SyndicateType { Patronal = 'patronal', Trabalhadores = 'trabalhadores' }`

---

#### `syndicate_bindings`

Many-to-many between legal entities and syndicates.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| legal_entity_id | foreignId | References `legal_entities.id` |
| syndicate_id | foreignId | References `syndicates.id` |
| created_at / updated_at | timestamps | |

**Unique constraint**: `(legal_entity_id, syndicate_id)`

---

## 5. Status Machines

All status transitions are enforced at the service layer. Backwards transitions throw `InvalidTransitionException`. Terminal states throw on any transition attempt.

### 5.1 PayrollCycle

```
[Aberto]
   │  (Admin marks PJ NF collection open)
   ▼
[AguardandoNfPj]  ──── SIDE EFFECT: Slack channel notification to PJ collaborators
   │  (All PJ invoices received or deadline passed)
   ▼
[AguardandoComissoes]
   │  (Commission data entered for all eligible collaborators)
   ▼
[EmRevisao]
   │  (Admin submits to accounting review)
   ▼
[ConferidoContabilidade]
   │  (Accounting firm confirms)
   ▼
[Fechado]  ◄── TERMINAL — no further transitions
```

**Transition Table**

| From | To | Trigger | Side Effects |
|------|----|---------|--------------|
| Aberto | AguardandoNfPj | Admin action | Slack channel notification to #pj-invoices |
| AguardandoNfPj | AguardandoComissoes | Admin action | Slack individual DMs to PJ collaborators without invoice |
| AguardandoComissoes | EmRevisao | Admin action | None |
| EmRevisao | ConferidoContabilidade | Admin action | None |
| ConferidoContabilidade | Fechado | Admin action | Totals aggregated; cycle locked |

---

### 5.2 VacationBatch

```
[Rascunho]
   │  (Admin triggers eligibility calculation)
   ▼
[Calculado]  ──── SIDE EFFECT: VacationEligibilityService runs for all eligible collaborators
   │  (Admin reviews results)
   ▼
[EmRevisao]
   │  (Admin confirms batch)
   ▼
[Confirmado]
   │  (Vacation period completed)
   ▼
[Concluido]  ◄── TERMINAL
```

**Transition Table**

| From | To | Trigger | Side Effects |
|------|----|---------|--------------|
| Rascunho | Calculado | Admin action | `CalculateVacationEligibilityBatchJob` dispatched |
| Calculado | EmRevisao | Admin action | None |
| EmRevisao | Confirmado | Admin action | Vacation notices generated |
| Confirmado | Concluido | Admin action | None |

---

### 5.3 AdmissionChecklist

```
[Pendente]
   │  (First item confirmed)
   ▼
[EmAndamento]
   │  (All required items confirmed)          │  (Deadline passes with items remaining)
   ▼                                          ▼
[Completo]  ◄── TERMINAL              [Bloqueado]  ◄── TERMINAL
```

**Transition Table**

| From | To | Trigger |
|------|----|---------|
| Pendente | EmAndamento | First checklist item confirmed |
| EmAndamento | Completo | All mandatory items confirmed |
| EmAndamento | Bloqueado | `data_limite` passed without completion (scheduled check) |

---

### 5.4 TerminationRecord

```
[Iniciado]
   │  ALERT: Flash cancellation banner displayed until flash_cancelado = true
   ▼
[SimulacaoRealizada]
   │  ALERT: Flash cancellation banner
   ▼
[PreviaSolicitada]
   │  ALERT: Flash cancellation banner
   ▼
[PreviaConferida]
   │  ALERT: Flash cancellation banner (if flash_cancelado still false)
   ▼
[DocumentacaoEnviada]
   │  ALERT: Flash cancellation banner (if flash_cancelado still false)
   ▼
[Concluido]  ◄── TERMINAL
```

**Flash Cancellation Rule**: A persistent alert banner is displayed on the termination record view at every status step until `flash_cancelado = true`. This prevents the common omission of canceling the departing employee's Flash benefits card.

---

### 5.5 DissidioRound

```
[Rascunho]
   │  (Admin runs simulation)
   ▼
[Simulado]
   │  (Admin submits for approval)
   ▼
[AguardandoAprovacao]
   │  (Authorized admin approves and applies)
   ▼
[Aplicado]  ──── SIDE EFFECT: Mass salary update + ProfessionalHistoryEntry creation for all entries
   │  (Report generated)
   ▼
[RelatorioGerado]  ◄── TERMINAL
```

**Side Effects on `Aplicado`**:
1. Each `DissidioEntry` with status `simulado` → `aplicado`
2. `collaborators.salario_base` updated for all affected collaborators
3. A `ProfessionalHistoryEntry` created for each collaborator with `tipo_evento = Dissidio`
4. Retroactive differential computed and stored as `diferenca_retroativa` per entry

---

### 5.6 ThirteenthSalaryRound

```
[Aberto]
   │  (Admin simulates first installment)
   ▼
[PrimeiraParcelaSimulada]
   │  (First installment marked paid)
   ▼
[PrimeiraParcelaPaga]
   │  (Admin simulates second installment)
   ▼
[SegundaParcelaSimulada]
   │  (Second installment marked paid)
   ▼
[SegundaParcelaPaga]
   │  (Admin closes round)
   ▼
[Concluido]  ◄── TERMINAL
```

**Business Rule**: First installment (due Nov 30) = 50% of calculated value with **no INSS or IRRF deductions**. Second installment (due Dec 20) = remaining balance minus INSS and IRRF computed on the full annual base.

---

### 5.7 PlrRound

```
[Rascunho]
   │  (Policy document uploaded)
   ▼
[DocumentoEnviado]
   │  (Workers' Committee formed)
   ▼
[ComiteCriado]
   │  (Document sent to union)
   ▼
[AguardandoSindicato]
   │  (Union approves)
   ▼
[Aprovado]
   │  (Admin runs simulation)
   ▼
[Simulado]
   │  (Admin confirms payment)
   ▼
[Pago]  ◄── TERMINAL
```

---

## 6. Architecture Details

### 6.1 Service Classes (Deterministic Calculations)

All calculation services are pure PHP classes in `app/Services/`. They receive primitive inputs and return value objects or arrays. They have **no side effects** and are fully unit-testable.

---

#### `VacationEligibilityService`

**Purpose**: Determine which collaborators are eligible for vacation in a given batch and compute entitlements.

**Business Rules**:
- CLT: 12-month accrual period → 30 days vacation; batches processed in October (for Nov-Dec departure) and May (for Jun departure)
- Estagiário: 6-month accrual period → 15 days; batches processed in December and June
- PJ and Sócio: explicitly excluded from vacation batches
- Eligibility: `data_admissao` must be at least `periodo_aquisitivo_minimo_meses` months before batch reference date
- Vacation pay = `(salario_base / 30) * dias_ferias`
- Constitutional 1/3 = `valor_ferias * (1/3)`

**Methods**:
- `computeEligibility(Collaborator $c, Carbon $referenceDate): VacationEligibilityResult`
- `computeVacationPay(Collaborator $c): VacationPayResult`
- `filterEligibleCollaborators(Collection $collaborators, Carbon $referenceDate, ContractType $type): Collection`

---

#### `CommissionCalculationService`

**Purpose**: Calculate commission values for Closer and Advisor contract types.

**Closer Logic**:
- Raw commission received from external source
- DSR = `commission / business_days_in_month * rest_days_in_month`
- Total commission = `commission + DSR`

**Advisor Logic**:
- Commission received from external source
- Effective commission = `max(commission, minimo_garantido) - pro_labore_net`
- Days are counted by **B3 business days** (not calendar days) for proportionality
- New advisors in ramp-up: `minimo_garantido` applies for first N months

**Methods**:
- `calculateCloserCommission(string $rawCommission, int $businessDays, int $restDays): CommissionResult`
- `calculateAdvisorCommission(string $rawCommission, string $garantido, string $proLabore, int $b3Days, int $b3TotalDays): CommissionResult`

---

#### `B3BusinessDayService`

**Purpose**: Provide accurate business day counts using the B3 (Bolsa de Valores) calendar.

**Data**: Hardcoded Brazilian national holidays + B3-specific closure dates per year. Stored in config or seeded table.

**Methods**:
- `isBusinessDay(Carbon $date): bool`
- `countBusinessDays(Carbon $start, Carbon $end): int`
- `getBusinessDaysInMonth(int $year, int $month): int`
- `getRestDaysInMonth(int $year, int $month): int`

---

#### `TerminationCalculationService`

**Purpose**: Compute all financial components of a termination (rescisão).

**Components Calculated**:
1. **Proportional salary**: `(salario_base / 30) * dias_trabalhados_mes_final`
2. **Proportional vacation**: `(salario_base / 12) * meses_periodo_aquisitivo` + constitutional 1/3
3. **Proportional 13th**: `(salario_base / 12) * meses_trabalhados_no_ano`
4. **FGTS fine**: 40% of total FGTS balance for `DispensaSemJustaCausa`; 20% for `MutuoAcordo`; 0% for `PedidoDemissao` or `DispensaComJustaCausa`
5. **Notice period**: 30 days + 3 days per year of service (capped at 90 days)
6. **Flash benefit adjustment**: prorate remaining month benefit value

**Methods**:
- `simulate(Collaborator $c, TerminationType $type, Carbon $effectiveDate): TerminationSimulation`
- `calculateFgtsFine(string $fgtsBalance, TerminationType $type): string`
- `calculateNoticeIndemnity(Collaborator $c, TerminationType $type): string`

---

#### `PayrollConsolidationService`

**Purpose**: Aggregate all payroll entries for a cycle into totals by legal entity.

**Methods**:
- `consolidate(PayrollCycle $cycle): ConsolidationResult`
- `getPjInvoiceStatus(PayrollCycle $cycle): array` — returns per-PJ-collaborator invoice status
- `getEntryCompletionStatus(PayrollCycle $cycle): CompletionStatus`

---

#### `DissidioSimulationService`

**Purpose**: Simulate mass percentage application across all eligible CLT collaborators.

**Business Rules**:
- Applies to all active CLT (and optionally Estagiário) collaborators
- New salary = `floor(salario_anterior * (1 + percentual) * 100) / 100` (rounds down to cent)
- Retroactive differential = `(salario_novo - salario_anterior) * meses_retroativos`
- Retroactive paid as **Abono Pecuniário** (lump sum, not incorporated into salary history until Aplicado)
- Grouped by legal entity for accounting breakdown

**Methods**:
- `simulate(DissidioRound $round): DissidioSimulationResult`
- `apply(DissidioRound $round): void` — creates history entries, updates salaries, transitions status

---

#### `ThirteenthSalaryCalculationService`

**Purpose**: Compute both installments of the 13th salary.

**Rules**:
- Eligible: CLT collaborators active for at least 1 month in the reference year
- `meses_trabalhados`: count months where collaborator was active; partial month > 15 days counts as full
- `media_comissoes`: average of commissions across months worked (zero if no commission)
- `base_calculo = salario_base + media_comissoes`
- `valor_integral = (base_calculo / 12) * meses_trabalhados`
- `primeira_parcela = valor_integral * 0.5` (no deductions)
- `segunda_parcela = valor_integral - primeira_parcela - desconto_inss - desconto_irrf`
- INSS and IRRF computed on full `valor_integral` for second installment

**Methods**:
- `calculateEntry(Collaborator $c, int $year): ThirteenthEntry`
- `calculateInstallments(ThirteenthEntry $entry): ThirteenthInstallments`

---

#### `PlrSimulatorService`

**Purpose**: Simulate PLR distribution.

**Rules**:
- Eligible: CLT collaborators who worked at least 6 months in the year
- Distribution proportional to `(media_salarios_ano * meses_trabalhados)` as share of total
- IRRF uses special PLR tax table (not standard IRRF table)
- PJ and Sócio excluded

**Methods**:
- `simulate(PlrRound $round, string $totalAmount): PlrSimulationResult`
- `calculateIrrf(string $plrValue): string` — uses PLR-specific IRRF aliquot table

---

### 6.2 AI Agent Architecture

**Package**: `laravel/ai` v0.5.1 (`composer require laravel/ai`) — built on `prism-php/prism`, provider-agnostic.

**Installation**: `composer require laravel/ai`

**Configuration**: Set `AI_DEFAULT_PROVIDER` and the matching API key in `.env`. All providers are configured in `config/ai.php`. Override model with `AI_DEFAULT_MODEL`. Supported providers: `openai`, `anthropic`, `groq`, `gemini`, `deepseek`, `mistral`, `ollama`, `openrouter`, `xai`, `azure`.

**Agent pattern**: Each agent implements `Laravel\Ai\Contracts\Agent` and uses the `Laravel\Ai\Promptable` trait. The `instructions()` method returns the system prompt string. For tool-calling agents, also implement `Laravel\Ai\Contracts\HasTools` and return `Tool[]` from `tools()`. Call `(new MyAgent)->prompt($userMessage, provider: config('ai.default'), model: config('ai.default_model'))` to invoke.

Following the **Nexus EnpsAnalystAgent pattern**: each agent uses the Laravel AI SDK's `Agent` contract and `Promptable` trait. All prompts are in Portuguese. Agents are minimal — 3 genuine AI agents total; everything else is deterministic PHP logic.

#### `PayrollDiscrepancyAnalystAgent`

**Type**: One-shot (no conversation history)

**Input**: Consolidated payroll data vs. accounting firm's figures

**Output**: Structured Portuguese-language discrepancy summary with variance table

**Prompt**: Provides context about each legal entity's totals, flags percentage deviations > 1%, suggests likely causes

**Laravel AI SDK Usage**:
```php
class PayrollDiscrepancyAnalystAgent implements Agent
{
    use Promptable;

    // instructions() returns the system prompt (Portuguese)
    public function instructions(): string { /* Portuguese system prompt */ }

    public function analyze(array $payrollData, array $accountingData): DiscrepancyReport
    {
        // Call: $this->prompt(json_encode(compact('payrollData','accountingData')))
        // Returns: structured text; parse into DiscrepancyReport value object
    }
}
```

---

#### `DpAssistantAgent`

**Type**: Conversational with tool calls (`HasTools`)

**Purpose**: Answers admin questions about DP processes in natural language. Invokes focused tools on demand — no data is pre-loaded into context.

**Implements**: `Agent`, `HasTools`, uses `Promptable` trait.

**Tools registered** (in `app/Ai/Tools/`):

| Tool class | Description | Data returned |
|---|---|---|
| `VacationEligibilityTool` | Eligible CLT (12mo) and intern (6mo) collaborators not yet in active batches | Names, hire dates, roles, active batch info |
| `PayrollStatusTool` | Last 6 payroll cycles with totals and pending PJ invoice counts | Gross, net, PJ totals, NF pending per cycle |
| `CollaboratorStatsTool` | Headcount by contract type and department; recent hires/terminations | Active count, by-type, by-dept, last 3 months movements |
| `DissidioInfoTool` | Last 5 dissidio rounds with percentages and data-base dates | Year, percentage, status, data-base |
| `AnnualObligationsTool` | Last 3 rounds each of 13th salary and PLR | Statuses, deadlines, participant counts, PLR totals |

**System instructions**: Lightweight — rules only (respond in Portuguese, use tools before answering, show step-by-step reasoning, format with Markdown). Brazilian labor law rules embedded (INSS tables, IRRF, DSR, vacation accrual, 13th pro-rata, PLR IRRF table, contribuição assistencial).

**Full documentation**: See `DP_ASSISTANT_AGENT.md` at project root.

**Memory**: Conversation history retained per session (no cross-session persistence in hackathon)

---

#### `SimulationReportAgent`

**Type**: One-shot

**Input**: Simulation result data (dissídio, 13th salary, or PLR)

**Output**: Executive summary report in Portuguese for management/accounting audience; includes narrative, key metrics, and risk highlights

**Used by**: Admin-facing "Generate Report" buttons on simulation pages

---

### 6.3 Slack Integration (Simulated for Hackathon)

**`SlackNotificationService`** in `app/Services/`:

```php
class SlackNotificationService
{
    public function __construct(private bool $simulate = false) {}

    public function sendChannelMessage(string $channel, string $message): void
    {
        if ($this->simulate) {
            Log::channel('slack')->info("CHANNEL:{$channel}", ['message' => $message]);
            return;
        }
        // Real Slack API call
    }

    public function sendDirectMessage(string $slackUserId, string $message): void { ... }
}
```

**`SLACK_SIMULATE=true`** in `.env` activates simulation mode.

**Notification Templates** (Portuguese):

1. **PJ Invoice Channel Reminder** (sent last week of month to #departamento-pessoal):
   > "Atenção colaboradores PJ! O prazo para envio das notas fiscais de [MÊS] encerra em [DATA]. Por favor, acesse o portal Eva e faça o upload da sua NF. Contagem: [N] de [TOTAL] notas recebidas."

2. **PJ Invoice Individual DM** (sent to collaborators without invoice, penultimate business day):
   > "Olá [NOME]! Identificamos que sua nota fiscal de [MÊS] ainda não foi enviada. O prazo encerra amanhã. Acesse: [LINK]"

---

### 6.4 Queue / Job Architecture

**Three queue priorities**: `high`, `default`, `slack`

| Job | Queue | Description |
|-----|-------|-------------|
| `ProcessPayrollConsolidationJob` | high | Aggregates totals after entries updated |
| `SimulateDissidioImpactJob` | default | Runs dissídio simulation for all collaborators |
| `SimulateThirteenthSalaryJob` | default | Calculates all 13th salary entries |
| `CalculateVacationEligibilityBatchJob` | default | Runs eligibility for a vacation batch |
| `GeneratePayrollDiscrepancyReportJob` | default | Invokes AI agent for discrepancy analysis |
| `SendPjInvoiceChannelReminderJob` | slack | Sends channel reminder |
| `SendPjInvoiceIndividualRemindersJob` | slack | Sends individual DMs to non-compliant PJ collaborators |

**Scheduling** (in `routes/console.php` or `Console/Kernel.php`):
- `SendPjInvoiceChannelReminderJob`: weekly, last 7 days of each month
- `SendPjInvoiceIndividualRemindersJob`: daily on penultimate business day of month

---

## 7. Frontend Architecture

### 7.1 Page Inventory

```
resources/js/pages/
├── Dashboard.vue                          # Summary metrics, pending actions
├── collaborators/
│   ├── Index.vue                          # Admin: searchable/filterable table
│   ├── Create.vue                         # Admin: multi-section creation form
│   ├── Edit.vue                           # Admin: pre-filled edit form
│   └── Show.vue                           # Admin: detail view + history timeline
├── self-service/
│   ├── Profile.vue                        # Collaborator: read-only own data
│   └── Invoices.vue                       # PJ collaborator: invoice upload area
├── payroll-cycles/
│   ├── Index.vue                          # Admin: cycle list with status badges
│   ├── Show.vue                           # Admin: entries table + invoice consolidation
│   └── Commissions.vue                    # Admin: commission entry panel
├── vacation-batches/
│   ├── Index.vue                          # Admin: batch list
│   └── Show.vue                           # Admin: eligibility list with actions
├── admission-checklists/
│   └── Show.vue                           # Admin: checklist management view
├── dissidio/
│   ├── Index.vue                          # Admin: round list
│   ├── Simulate.vue                       # Admin: simulation preview and confirm
│   └── Report.vue                         # Admin: accounting report view
├── thirteenth-salary/
│   ├── Index.vue                          # Admin: round list
│   └── Simulate.vue                       # Admin: installment calculator
├── plr/
│   ├── Index.vue                          # Admin: PLR round list
│   ├── Simulate.vue                       # Admin: distribution simulator
│   └── Committee.vue                      # Admin: Workers' Committee management
├── union/
│   └── Opposition.vue                     # Admin: opposition letter flag list
└── settings/
    ├── Profile.vue                        # Existing (user profile)
    └── Appearance.vue                     # Existing (theme/appearance)
```

---

### 7.2 Key Components

Located in `resources/js/components/`:

| Component | Purpose |
|-----------|---------|
| `CollaboratorTable.vue` | Reusable data table with search, multi-filter (status, contract type, legal entity, department), pagination, and row actions |
| `FlashBenefitForm.vue` | Grouped form section for all Flash benefit fields with totaling |
| `StatusBadge.vue` | Colored badge for all status enums; maps enum values to Tailwind color variants |
| `HistoryTimeline.vue` | Timeline view of `ProfessionalHistoryEntry` records with event type icons |
| `SimulationPreview.vue` | Reusable results table for dissídio, 13th, and PLR simulations |
| `InvoiceUpload.vue` | Drag-and-drop PDF upload component with progress indicator and file validation feedback |
| `PayrollEntryRow.vue` | Single payroll line item with inline editing for commission and deduction fields |
| `TerminationAlert.vue` | Persistent flash cancellation banner shown on termination view until `flash_cancelado = true` |
| `CycleStatusStepper.vue` | Visual stepper showing current payroll cycle status and available transitions |

---

### 7.3 Key Composables

Located in `resources/js/composables/`:

```typescript
// useCollaboratorFilters.ts
export function useCollaboratorFilters() {
  const search = ref('')
  const statusFilter = ref<CollaboratorStatus | ''>('')
  const contractTypeFilter = ref<ContractType | ''>('')
  const legalEntityFilter = ref<number | null>(null)
  const filters = computed(() => ({...}))
  const reset = () => { /* reset all */ }
  return { search, statusFilter, contractTypeFilter, legalEntityFilter, filters, reset }
}

// usePayrollCycle.ts
export function usePayrollCycle(cycleId: number) {
  const cycle = ref<PayrollCycle | null>(null)
  const pendingItems = computed(() => [...])
  const canTransition = computed(() => [...])
  const transition = async (to: PayrollCycleStatus) => { /* Inertia PUT */ }
  return { cycle, pendingItems, canTransition, transition }
}

// useSimulation.ts
export function useSimulation<T>() {
  const isLoading = ref(false)
  const results = ref<T | null>(null)
  const error = ref<string | null>(null)
  const run = async (endpoint: string, payload: object) => { /* Inertia POST */ }
  const confirm = async (endpoint: string) => { /* Inertia POST */ }
  const reset = () => { results.value = null; error.value = null }
  return { isLoading, results, error, run, confirm, reset }
}
```

---

### 7.4 TypeScript Types

Generated by **Laravel Wayfinder** for routes. Domain types manually defined in `resources/js/types/`:

```typescript
// resources/js/types/domain.ts
export type ContractType = 'clt' | 'pj' | 'estagiario' | 'socio'
export type CollaboratorStatus = 'ativo' | 'afastado' | 'desligado'
export type PayrollCycleStatus = 'aberto' | 'aguardando_nf_pj' | 'aguardando_comissoes' | 'em_revisao' | 'conferido_contabilidade' | 'fechado'
// ... all enums mirrored
```

---

## 8. Phase Plan

### Phase 1 — Foundation & Central Directory (3 PRs)

**Goal**: Establish the project skeleton, authentication, and the core collaborator directory. Admins can create and manage all collaborator profiles; collaborators can log in and view their own data.

**PR 1.1 — Project Skeleton & Auth**

- [ ] Laravel 13 project created with Vue 3 + Inertia.js v3 starter
- [ ] Tailwind CSS 4 + shadcn/vue (reka-ui) configured
- [ ] Lucide Vue Next installed
- [ ] Laravel Fortify installed; registration route disabled
- [ ] `UserRole` enum and `role` / `collaborator_id` columns added to `users` table
- [ ] Admin gate registered in `AuthServiceProvider`
- [ ] `AdminMiddleware` protecting admin routes
- [ ] `CollaboratorMiddleware` protecting self-service routes
- [ ] Route groups defined: `admin.*` and `self-service.*`
- [ ] Dashboard.vue skeleton with role-aware navigation
- [ ] Pest 4 configured with `RefreshDatabase` trait
- [ ] Base test for auth: admin can access admin routes, collaborator cannot

**Validation**: `php artisan test` passes; admin login works; collaborator login shows limited nav

---

**PR 1.2 — Legal Entities & Collaborator Directory**

- [ ] `legal_entities` migration and model with factory
- [ ] `LegalEntity` seeder with 5 entities (holding, educacao, consultoria, gestora, corretora)
- [ ] All collaborator-related migrations: `collaborators`, `flash_benefit_profiles`, `professional_history_entries`
- [ ] All collaborator enums in `app/Enums/`
- [ ] `Collaborator` model with relationships, `ContractType`/`CollaboratorStatus`/`CommissionType` casts
- [ ] `ProfessionalHistoryEntry` model with immutability enforcement + `ImmutableModelException`
- [ ] `FlashBenefitProfile` model
- [ ] `CollaboratorPolicy` with admin-only write actions
- [ ] `CollaboratorController`: index, create, store, show, edit, update, destroy (soft delete)
- [ ] `CollaboratorTable.vue` component with search/filter/pagination
- [ ] `collaborators/Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` pages
- [ ] `FlashBenefitForm.vue` component
- [ ] `HistoryTimeline.vue` component
- [ ] CPF validation rule (`app/Rules/ValidCpf.php`)
- [ ] Factories for all new models
- [ ] Pest tests for all controller actions

**Validation**: Admin can CRUD all collaborators; CPF validation rejects invalid CPFs; soft delete works; timeline shows history

---

**PR 1.3 — Self-Service Portal & PJ Invoice Upload**

- [ ] Self-service route group secured to authenticated collaborators only
- [ ] `self-service/Profile.vue` — read-only view of collaborator's own data
- [ ] `PjInvoiceController` (collaborator scope: only own invoices for active cycle)
- [ ] `pj_invoices` migration and `PjInvoice` model
- [ ] `InvoiceUpload.vue` component with PDF validation (MIME + size)
- [ ] File storage configured for private PDF storage
- [ ] Signed URL generation for secure PDF access
- [ ] `self-service/Invoices.vue` page
- [ ] `PjInvoicePolicy`: collaborator can only upload their own invoice for an open cycle
- [ ] Pest tests for upload validation (rejects non-PDF, rejects > 10MB, rejects if cycle not in correct status)

**Validation**: PJ collaborator can upload invoice; non-PJ collaborator cannot see invoice upload; invalid files rejected with clear error messages

---

### Phase 2 — Payroll Orchestration & PJ Portal (3 PRs)

**Goal**: Full payroll cycle management for admins: open cycles, track invoice status, enter commissions, advance status machine, close cycles.

**PR 2.1 — Payroll Cycle Foundation**

- [ ] `payroll_cycles` and `payroll_entries` migrations and models
- [ ] All payroll enums
- [ ] `PayrollCycleService` with status machine and transition validation
- [ ] `payroll_cycle_events` table for transition audit log
- [ ] `PayrollCycleController`: index, store (open new cycle), show, update (status transitions)
- [ ] `PayrollEntryController`: store (create entries), update (edit entry data)
- [ ] `payroll-cycles/Index.vue` and `Show.vue` pages
- [ ] `CycleStatusStepper.vue` component
- [ ] `PayrollEntryRow.vue` component with inline editing
- [ ] `ProcessPayrollConsolidationJob` queued job
- [ ] Pest tests for all status transitions (valid and invalid)

**Validation**: Admin can open a cycle; entries can be created and edited; invalid transitions throw exceptions

---

**PR 2.2 — Commission Entry Panel**

- [ ] `CommissionCalculationService` with Closer and Advisor logic
- [ ] `B3BusinessDayService` with 2024/2025/2026 holiday data
- [ ] `payroll-cycles/Commissions.vue` page with per-collaborator commission entry
- [ ] Commission calculation preview (calculate DSR in real-time on UI)
- [ ] `usePayrollCycle` composable
- [ ] Pest unit tests for `CommissionCalculationService` (DSR calc, advisor min guarantee)
- [ ] Pest unit tests for `B3BusinessDayService`

**Validation**: DSR calculation correct for sample data; advisor min guarantee applies; B3 calendar correctly excludes holidays

---

**PR 2.3 — Payroll Consolidation & Slack Notifications**

- [ ] `PayrollConsolidationService`
- [ ] Admin view: PJ invoice status tracker in `payroll-cycles/Show.vue`
- [ ] `SlackNotificationService` with simulate mode
- [ ] `SendPjInvoiceChannelReminderJob`
- [ ] `SendPjInvoiceIndividualRemindersJob`
- [ ] Scheduler configuration for reminder jobs
- [ ] `SLACK_SIMULATE` environment variable respected
- [ ] Pest tests for consolidation service
- [ ] Pest tests for Slack simulate mode (verifies log entries, not actual Slack calls)

**Validation**: Consolidation totals match sum of entries; simulate mode logs instead of calling Slack API

---

### Phase 3 — Checklists & Vacation Batches (3 PRs)

**Goal**: Admission workflow management and vacation eligibility processing.

**PR 3.1 — Admission Checklists**

- [ ] `admission_checklists` and `admission_checklist_items` migrations and models
- [ ] `ChecklistStatus` enum and status machine
- [ ] Auto-create checklist on collaborator `store` action
- [ ] Predefined checklist item templates per `ContractType` (seeded)
- [ ] `AdmissionChecklistController`
- [ ] `admission-checklists/Show.vue` page with item confirmation UI
- [ ] Deadline tracking with `Bloqueado` transition
- [ ] Pest tests for checklist creation, item confirmation, status transitions

**Validation**: Checklist auto-created on collaborator creation; all mandatory items confirmed → Completo; past deadline → Bloqueado

---

**PR 3.2 — Vacation Batches**

- [ ] `vacation_batches` and `vacation_batch_collaborators` migrations and models
- [ ] `VacationEligibilityService` with CLT and Estagiário rules
- [ ] `CalculateVacationEligibilityBatchJob`
- [ ] `VacationBatchController`: index, store, show, status transitions
- [ ] `vacation-batches/Index.vue` and `Show.vue` pages
- [ ] Eligibility list with eligible/ineligible split and vacation pay preview
- [ ] `useSimulation` composable integration
- [ ] Pest tests for `VacationEligibilityService` (edge cases: partial months, exact 12-month boundary)

**Validation**: Correct eligibility computed for sample data; CLT and estagiário rules applied separately; vacation pay calculation correct

---

**PR 3.3 — Termination Records**

- [ ] `termination_records` migration and model
- [ ] `TerminationCalculationService`
- [ ] `TerminationController`: create, store, show, update (status transitions)
- [ ] `TerminationAlert.vue` flash cancellation banner
- [ ] Status machine with flash cancellation tracking
- [ ] Automatic `CollaboratorStatus` update to `Desligado` on `Concluido`
- [ ] Pest tests for all FGTS fine scenarios and notice period calculations

**Validation**: FGTS fine correctly 40%/20%/0% by termination type; flash banner shown until `flash_cancelado = true`; collaborator status updated on completion

---

### Phase 4 — Contractual Events, Dissídio & Annual Obligations (3 PRs)

**Goal**: Annual HR obligations: collective wage adjustment, 13th salary, PLR, and union contribution management.

**PR 4.1 — Dissídio**

- [ ] `dissidio_rounds` and `dissidio_entries` migrations and models
- [ ] `DissidioSimulationService` with retroactive differential calculation
- [ ] `SimulateDissidioImpactJob`
- [ ] `DissidioController`: index, store, show, simulate action, apply action
- [ ] `dissidio/Index.vue`, `Simulate.vue`, `Report.vue` pages
- [ ] `SimulationPreview.vue` component
- [ ] `SimulationReportAgent` AI agent integration
- [ ] Mass salary update + `ProfessionalHistoryEntry` creation on Aplicado
- [ ] Pest tests for simulation accuracy and history entry creation

**Validation**: Simulation percentage correctly applied; retroactive differential calculated; applying round creates history entries and updates salaries

---

**PR 4.2 — 13th Salary & PLR**

- [ ] `thirteenth_salary_rounds`, `thirteenth_salary_entries` migrations and models
- [ ] `ThirteenthSalaryCalculationService` with two-installment logic
- [ ] `SimulateThirteenthSalaryJob`
- [ ] `ThirteenthSalaryController`
- [ ] `thirteenth-salary/Index.vue` and `Simulate.vue` pages
- [ ] `plr_rounds`, `plr_entries`, `plr_committee_members` migrations and models
- [ ] `PlrSimulatorService`
- [ ] `PlrController`
- [ ] `plr/Index.vue`, `Simulate.vue`, `Committee.vue` pages
- [ ] Pest tests for both services (partial year, commission averages)

**Validation**: 13th installment calculations correct; PLR distribution proportional; Workers' Committee management functional

---

**PR 4.3 — Union Obligations & AI Integration**

- [ ] `assistive_convention_records`, `syndicates`, `syndicate_bindings` migrations and models
- [ ] `AssistiveConventionController`
- [ ] `union/Opposition.vue` page with opposition letter tracking
- [ ] `PayrollDiscrepancyAnalystAgent` AI agent
- [ ] `DpAssistantAgent` with tool definitions
- [ ] `GeneratePayrollDiscrepancyReportJob`
- [ ] AI agent integration in payroll cycle `Show.vue` page
- [ ] DP Assistant chat widget in Dashboard
- [ ] Pest tests for assistive convention tracking
- [ ] Integration smoke test for AI agents (mocked LLM responses)

**Validation**: Opposition letter tracking functional; AI discrepancy report generates; DP Assistant responds to Portuguese questions

---

### Phase 5 — Realistic Demo Fixtures (1 PR)

**Goal:** Populate every module with coherent, realistic Brazilian data so any developer can run `php artisan migrate:fresh --seed` and immediately QA the full application end-to-end.

**PR 5.1 — Demo Fixtures**

- [ ] `.env.example` — set `APP_FAKER_LOCALE=pt_BR`
- [ ] `CollaboratorSeeder` — force `data_admissao` to `-3 years` / `-18 months` range to guarantee vacation eligibility
- [ ] `SyndicateSeeder` — 4 syndicates (2 patronal, 2 trabalhadores) bound to all 5 legal entities
- [ ] `PayrollCycleSeeder` — 3 payroll cycles: Jan/2025 Fechado (with 3 CLT `PayrollEntry` records), Feb/2025 ConferidoContabilidade, Mar/2025 Aberto
- [ ] `VacationBatchSeeder` — CLT batch for Mar/2025, eligibility computed from `data_admissao`, `valor_ferias` and `valor_terco_constitucional` populated
- [ ] `DissidioSeeder` — 2025 round at 4.5%, status Aplicado, one `DissidioEntry` + one `ProfessionalHistoryEntry` per CLT collaborator
- [ ] `ThirteenthSalarySeeder` — 2025 round in SegundaParcelaSimulada, full entries with progressive 2025 INSS/IRRF calculations
- [ ] `PlrSeeder` — 2025 round Simulado, entries weighted by `salario_base × meses_trabalhados`, PLR IRRF table applied, 3 committee members
- [ ] `AssistiveConventionSeeder` — ~30% of CLT collaborators with records; ~40% of those have `fez_oposicao=true`
- [ ] `DatabaseSeeder` — calls all domain seeders in dependency order after `CollaboratorSeeder`

**Validation**: `php artisan migrate:fresh --seed` completes without errors; every HR module shows pre-populated data; `Collaborator::count()` ≥ 22; `DissidioEntry`, `ThirteenthSalaryEntry`, `PlrEntry` counts match active CLT collaborator count; vacation batch shows correct eligible/ineligible split.

---

## 9. Environment Variables Reference

```dotenv
# =============================================
# Application
# =============================================
APP_NAME=Eva
APP_ENV=local
APP_KEY=                          # Generated via php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost
APP_FAKER_LOCALE=pt_BR            # Brazilian names, CPFs, phone numbers in seeders

# =============================================
# Database
# =============================================
DB_CONNECTION=sqlite              # Use 'pgsql' for production
DB_DATABASE=/absolute/path/to/database.sqlite
# Production PostgreSQL:
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=eva_production
# DB_USERNAME=eva
# DB_PASSWORD=

# =============================================
# Queue
# =============================================
QUEUE_CONNECTION=database         # Use 'redis' for production
# Production Redis:
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379

# =============================================
# AI (Laravel AI SDK)
# =============================================
AI_DEFAULT_PROVIDER=openai        # openai | anthropic | groq | gemini | deepseek | mistral | ollama | openrouter | xai | azure
AI_DEFAULT_MODEL=gpt-4o-mini      # Any model supported by the chosen provider
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
GROQ_API_KEY=
GEMINI_API_KEY=
# See config/ai.php for full provider configuration

# =============================================
# Slack Integration
# =============================================
SLACK_SIMULATE=true               # Set to false in production with real Slack app
SLACK_BOT_TOKEN=                  # xoxb- token
SLACK_SIGNING_SECRET=
SLACK_PJ_INVOICES_CHANNEL=#departamento-pessoal

# =============================================
# File Storage
# =============================================
FILESYSTEM_DISK=local             # Use 's3' for production
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=eva-documents
AWS_URL=

# =============================================
# Session & Cache
# =============================================
SESSION_DRIVER=database
CACHE_STORE=database

# =============================================
# Logging
# =============================================
LOG_CHANNEL=stack
LOG_SLACK_CHANNEL=slack           # Dedicated channel for Slack simulation logs
```

---

## 10. AI Planning Rules

The following rules MUST be followed by any AI agent generating code or making implementation decisions for Eva.

1. **One phase at a time.** Complete and merge all PRs in a phase before beginning the next phase. Do not skip ahead.

2. **Every model must have a factory. Every controller action must have a Pest test.** No exceptions. If a factory is trivial, it is still required.

3. **Domain field names in Portuguese; class names, methods, and variables in English.** Database columns and migration field names use Portuguese (e.g., `salario_base`, `tipo_contrato`, `data_admissao`). PHP and TypeScript identifiers use English (e.g., `$collaborator->baseSalary` via accessor, `calculateGrossSalary()`, `contractType`).

4. **String-backed enums in `app/Enums/` with `label()` method.** Every enum must have a `label(): string` method returning the Portuguese display text for UI use. Example: `ContractType::Clt->label()` returns `'CLT'`.

5. **Vue pages use `<script setup lang="ts">` with Composition API.** No Options API. No `defineComponent`. Props typed with TypeScript interfaces. Emits typed.

6. **Immutable models enforce immutability at the model level.** `ProfessionalHistoryEntry` overrides `save()`, `update()`, and `delete()` in the Eloquent model to throw `ImmutableModelException`. This must not be bypassed via `DB::table()` in application code.

7. **All decimal monetary values use `decimal(12,2)` in migrations — never floats.** In PHP, use `bcmath` functions for arithmetic on monetary values. Never use `+`, `-`, `*`, `/` operators directly on money strings. In TypeScript, display only; never calculate.

8. **Status machines enforce transitions at the service layer.** No status field is ever set directly via `$model->status = $newStatus`. All transitions go through the appropriate service class method, which validates the transition and may trigger side effects. Backwards transitions throw `InvalidTransitionException`. Terminal states throw on any attempted transition.

9. **Hackathon scope: simulation is acceptable for Slack and external integrations. Core calculations must be real.** `SLACK_SIMULATE=true` logs instead of calling Slack. Nexus integration can be stubbed. But `CommissionCalculationService`, `VacationEligibilityService`, `TerminationCalculationService`, `DissidioSimulationService`, `ThirteenthSalaryCalculationService`, and `PlrSimulatorService` must implement actual business logic — not stubs.

10. **Run all verification checks before every PR merge.** Checklist: `php artisan test` passes with zero failures, `./vendor/bin/pint --test` passes, `npm run type-check` passes (zero TypeScript errors), `npm run lint` passes, no `console.log`, `TODO`, `HACK`, or `debugger` statements in committed code.

---

## 11. Glossary

| Portuguese Term | English Explanation |
|----------------|---------------------|
| **Departamento Pessoal (DP)** | HR Department / Personal Department — the Brazilian term for the operational HR function handling payroll, admissions, terminations, and legal compliance |
| **Colaborador** | Employee or Collaborator — used generically for anyone on the company's workforce regardless of contract type |
| **CLT** | Consolidação das Leis do Trabalho — Brazil's primary labor law; a CLT contract is a formal employment contract with full statutory benefits (FGTS, INSS, férias, 13º salário) |
| **PJ** | Pessoa Jurídica — a contractor who operates through their own corporate entity; paid via invoice (Nota Fiscal) rather than payroll |
| **Estagiário** | Intern — governed by the Lei de Estágio (Law 11,788/2008), not the CLT; shorter accrual periods and reduced benefits |
| **Sócio** | Partner or Associate — equity-holding member of a legal entity, compensated via pro-labore rather than salary |
| **Flash** | Flash Benefícios — the benefits management platform used by Clube do Valor for food, meal, transportation, health, culture, education, and home office allowances |
| **Folha de Pagamento** | Payroll — the monthly payroll run covering all employees |
| **Dissídio** | Collective Wage Adjustment — an annual, union-negotiated percentage increase applied to all CLT salaries; retroactive differentials paid as Abono Pecuniário |
| **PLR** | Participação nos Lucros e Resultados — Profit and Results Sharing program; a mandatory Workers' Committee and union registration are required by law |
| **13º Salário** | 13th Salary — a mandatory year-end bonus for CLT employees equivalent to one month's salary; paid in two installments (November and December) |
| **DSR** | Descanso Semanal Remunerado — Paid Weekly Rest; a commission add-on that compensates CLT employees for commission earned on rest days |
| **INSS** | Instituto Nacional do Seguro Social — Brazil's social security system; both employee and employer contribute as a percentage of gross salary |
| **IRRF** | Imposto de Renda Retido na Fonte — Income Tax Withheld at Source; employer withholds and remits on behalf of employee using progressive tax tables |
| **FGTS** | Fundo de Garantia do Tempo de Serviço — Severance Guarantee Fund; employer deposits 8% of gross salary monthly; on involuntary dismissal, employer pays an additional 40% fine on total balance |
| **Convenção Coletiva** | Collective Labor Agreement — negotiated annually between employer unions and workers' unions; governs salary floors, dissídio percentages, and assistive contributions |
| **Contribuição Assistencial** | Union Assistive Contribution — a payroll deduction for union services; CLT employees can opt out by sending a Carta de Oposição within the prescribed window |
| **Carta de Oposição** | Opposition Letter — formal written notice from a collaborator opting out of union assistive contribution; must be sent via registered mail (AR) to both the workers' union and the employer |
| **Comitê de Trabalhadores** | Workers' Committee — a committee of elected employee representatives required by law to validate PLR programs; must include members from each legal entity |
| **Período Aquisitivo** | Accrual Period — the period during which vacation rights accumulate; 12 months for CLT (→ 30 days), 6 months for Estagiário (→ 15 days) |
| **Nota Fiscal (NF / NF-e)** | Invoice / Electronic Tax Invoice — the official Brazilian tax document that PJ contractors must issue for each service payment |
| **Abono Pecuniário** | Cash Allowance — a lump-sum cash payment; in the dissídio context, refers to the retroactive wage differential paid as a one-time bonus rather than incorporated into the salary history prior to the effective date |
| **Contabilidade** | Accounting firm — Clube do Valor's outsourced accounting partner that receives payroll summaries for tax filing and e-Social compliance |
| **Mínimo Garantido** | Guaranteed Minimum — a floor commission amount guaranteed to new Advisor-type employees during their ramp-up period; if actual commission falls below this amount, the minimum is paid instead |
| **B3** | Brasil, Bolsa, Balcão — the Brazilian stock exchange; its business day calendar (including exchange-specific closures beyond national holidays) is used to calculate proportional commissions for Advisor-type employees |
| **Rescisão** | Termination/Severance — the formal process of ending an employment relationship; includes calculation and payment of all statutory severance components |
| **Aviso Prévio** | Notice Period — statutory notice that must be given before termination; 30 days base + 3 days per year of service (capped at 90 days); can be worked or paid in lieu |
| **Exame Demissional** | Termination Medical Exam — mandatory medical examination required within the notice period for CLT contract terminations |
| **e-Social** | Brazilian government digital platform for unified reporting of labor, social security, and tax obligations; non-compliance fines up to R$ 6,000 per infraction |
| **Pro-labore** | Monthly compensation paid to company partners (Sócios) and PJ contractors; not subject to CLT rules but subject to INSS contribution |
| **Período de Férias** | Vacation Period — the 30-day (CLT) or 15-day (Estagiário) vacation leave period; must begin at least 2 days before a weekly rest day or public holiday |
| **Terço Constitucional** | Constitutional One-Third — a mandatory 1/3 addition to vacation pay guaranteed by the Brazilian Federal Constitution (Art. 7, XVII) |
