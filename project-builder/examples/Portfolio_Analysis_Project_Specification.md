# Portfolio Analysis — Investment Portfolio Extraction & Classification Platform

## Project Specification & AI Planning Guide

---

## 1. Project Overview

**Portfolio Analysis** is a web application for investment advisors and analysts that need to extract, classify, and review investment portfolio positions from uploaded documents. The system uses AI-powered multimodal extraction to read PDFs, images, and spreadsheets, then classifies each asset into Brazilian investment categories using a 3-tier deterministic + AI pipeline.

The application replaces a headless Python webhook service with a full-featured web interface built on Laravel 13 and React 19, adding user management, a classification rule editor, real-time processing updates, and a portfolio review workspace.

### Core Value Proposition

1. Upload portfolio documents in any format (PDF, image, CSV, Excel)
2. AI extracts individual asset positions automatically
3. Deterministic rules + AI classify each asset into Classe/Estratégia
4. Analysts review, correct, and approve classifications
5. Export clean, classified portfolio summaries

---

## 2. Core Technology Stack

| Layer | Technology | Version | Justification |
|---|---|---|---|
| Backend Framework | Laravel | 13.x | Queue orchestration, Inertia adapter, AI SDK integration |
| Frontend Framework | React | 19.x | Rich interactive components (upload zones, review tables, charts) |
| SPA Bridge | Inertia.js | 3.x | Server-driven routing with SPA-like UX; no separate API layer |
| Type-Safe Routes | Laravel Wayfinder | 0.1.x | Auto-generates typed TypeScript route functions |
| AI / LLM | Laravel AI SDK (`laravel/ai`) | latest | Provider-agnostic LLM abstraction (OpenAI, Anthropic, Gemini) |
| Authentication | Laravel Fortify | 1.x | Headless auth backend (login, register, 2FA, password reset) |
| Styling | Tailwind CSS | 4.x | Utility-first CSS with Vite plugin |
| Component Library | Radix UI | 1.x | Accessible, unstyled component primitives |
| Testing | Pest | 4.x | Modern PHP testing with expressive syntax |
| Database | SQLite | — | Simple, file-based, zero-config |
| Queue | Database driver | — | Async job processing without Redis dependency |
| Real-Time | Laravel Reverb | latest | First-party WebSocket server for live status updates |
| Build Tool | Vite | 8.x | Fast HMR and production builds |
| AI Dev Tooling | Laravel Boost | 2.x | MCP server, skills, documentation API for AI-assisted development |
| Code Style | Laravel Pint | 1.x | PHP code formatting |
| Icons | Lucide React | latest | Consistent icon library |

---

## 3. Non-Functional Constraints

### 3.1 Security

- **Authentication:** Laravel Fortify with 2FA (TOTP) support. Existing starter kit auth flow.
- **Authorization:** Role-based access via `UserRole` enum (`admin`, `analyst`, `viewer`). Enforced through Laravel Policies and Gates.
- **File Security:** Uploaded documents stored in `storage/app/private/` (not publicly accessible). Served via authenticated controller routes only.
- **Input Validation:** All file uploads validated for MIME type, size, and extension. Form Requests on every controller action.
- **AI Keys:** API keys stored exclusively in `.env`, never in code or database. Config accessed via `config('ai.*')`.

### 3.2 Performance

- **Upload:** Support batch uploads of up to 20 files per submission, max 50 MB per file.
- **Processing:** Documents processed asynchronously via queue. UI remains responsive during processing.
- **Extraction Timeout:** Individual document extraction capped at 5 minutes. Retries with exponential backoff (3 attempts).
- **Real-Time:** Status changes appear within 2 seconds of processing completion via Reverb WebSocket.

### 3.3 Reliability

- **Retry Logic:** All queue jobs retry 3 times with exponential backoff before marking as failed.
- **Idempotency:** Queue consumers check for existing `ExtractedAsset` records before creating duplicates. Re-dispatching a job produces the same result.
- **Partial Failure:** Individual document failure does not block other documents in the same submission. Submission reaches `PartiallyComplete` status.
- **AI Fallback:** If AI extraction fails, PHP-based parsers attempt extraction for structured files (CSV, Excel). If AI classification fails, only Base1 and deterministic results are used.

### 3.4 Observability

- **Processing Events:** Every status transition creates a `ProcessingEvent` record with trace ID, timestamps, and metadata.
- **Trace ID:** Each submission gets a UUID `trace_id` that propagates to all documents and processing events for end-to-end debugging.
- **Audit Log:** All user actions (upload, download, review, rule changes) logged with user, timestamp, IP, and action type.
- **Queue Health:** Admin dashboard shows pending/failed job counts from the `jobs` and `failed_jobs` tables.

---

## 4. Domain Model

### Entity Tree

```
User (with role: admin | analyst | viewer)
├── Submissions (batch uploads owned by user)
│   ├── Documents (individual files in a submission)
���   │   ├── ExtractedAssets (asset positions from a document)
│   │   └── ProcessingEvents (status audit trail)
│   └── ProcessingEvents (submission-level events)
├── ClassificationRules (Base1 reference data — admin managed)
└── AuditLogs (system-wide action log)
```

### 4.1 User

Existing model. Extended with `role` column.

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | Primary key |
| `name` | string | |
| `email` | string, unique | |
| `email_verified_at` | timestamp, nullable | |
| `password` | string | |
| `role` | string, default `viewer` | Enum: `admin`, `analyst`, `viewer` |
| `remember_token` | string, nullable | |
| `two_factor_*` | — | Existing Fortify 2FA columns |
| `timestamps` | — | |

**Enum:** `App\Enums\UserRole` — `Admin`, `Analyst`, `Viewer`

**Relationships:**
- `hasMany(Submission::class)`
- `hasMany(AuditLog::class)`

### 4.2 Submission

A batch upload of one or more documents.

| Column | Type | Notes |
|---|---|---|
| `id` | uuid, primary | |
| `user_id` | foreignId | Uploader |
| `email_lead` | string, nullable | Investor email (optional metadata) |
| `observation` | text, nullable | Free-text note |
| `status` | string | `SubmissionStatus` enum |
| `documents_count` | unsignedInteger, default 0 | Cached counter |
| `processed_documents_count` | unsignedInteger, default 0 | Cached counter |
| `failed_documents_count` | unsignedInteger, default 0 | Cached counter |
| `completed_at` | timestamp, nullable | When all documents finished |
| `error_summary` | text, nullable | Aggregated error info |
| `trace_id` | uuid | End-to-end debug tracing |
| `timestamps` | — | |
| `deleted_at` | softDeletes | |

**Enum `SubmissionStatus`:** `Pending`, `Processing`, `PartiallyComplete`, `Completed`, `Failed`

**Relationships:**
- `belongsTo(User::class)`
- `hasMany(Document::class)`
- `morphMany(ProcessingEvent::class, 'eventable')`

### 4.3 Document

An individual file within a submission.

| Column | Type | Notes |
|---|---|---|
| `id` | uuid, primary | |
| `submission_id` | foreignUuid | |
| `original_filename` | string | |
| `mime_type` | string | |
| `file_extension` | string | e.g., `.pdf`, `.csv` |
| `file_size_bytes` | unsignedBigInteger | |
| `storage_path` | string | Relative path within `private` disk |
| `status` | string | `DocumentStatus` enum |
| `is_processable` | boolean, default true | False for unsupported extensions |
| `page_count` | unsignedInteger, nullable | For PDFs |
| `extraction_method` | string, nullable | `ai_multimodal`, `ai_text`, `php_csv`, `php_excel` |
| `extracted_assets_count` | unsignedInteger, default 0 | Cached counter |
| `ai_model_used` | string, nullable | e.g., `gpt-4.1`, `claude-sonnet-4-20250514` |
| `ai_tokens_used` | unsignedInteger, nullable | Cost tracking |
| `error_message` | text, nullable | |
| `trace_id` | uuid | Inherited from submission |
| `timestamps` | — | |

**Relationships:**
- `belongsTo(Submission::class)`
- `hasMany(ExtractedAsset::class)`
- `morphMany(ProcessingEvent::class, 'eventable')`

### 4.4 ExtractedAsset

A single asset position extracted from a document.

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `document_id` | foreignUuid | |
| `submission_id` | foreignUuid | Denormalized for query performance |
| `ativo` | string | Asset name/ticker as extracted |
| `ticker` | string, nullable | Normalized B3 ticker when detected |
| `posicao` | string | Position value (Brazilian format, e.g., `59.000,00`) |
| `posicao_numeric` | decimal(18,2), nullable | Parsed numeric value for sorting/totals |
| `classe` | string, nullable | Classification Classe |
| `estrategia` | string, nullable | Classification Estratégia |
| `confidence` | decimal(3,2), nullable | AI classification confidence 0.00–1.00 |
| `classification_source` | string, nullable | `base1`, `deterministic`, `ai`, `manual` |
| `is_reviewed` | boolean, default false | Analyst has reviewed |
| `reviewed_by` | foreignId, nullable | |
| `reviewed_at` | timestamp, nullable | |
| `original_classe` | string, nullable | Pre-review value for audit trail |
| `original_estrategia` | string, nullable | Pre-review value for audit trail |
| `timestamps` | — | |

**Enum `ClassificationSource`:** `Base1`, `Deterministic`, `Ai`, `Manual`

**Relationships:**
- `belongsTo(Document::class)`
- `belongsTo(Submission::class)`
- `belongsTo(User::class, 'reviewed_by')`

### 4.5 ClassificationRule

Database equivalent of the Python `data/base1.csv`. Admin-managed reference data for deterministic classification.

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `chave` | string | Lookup key (ticker, asset name, or pattern) |
| `chave_normalized` | string, index | Uppercased/trimmed for matching |
| `classe` | string | Must be from allowed Classe set |
| `estrategia` | string | Must be from allowed Estratégia set |
| `match_type` | string, default `exact` | `exact`, `ticker_prefix`, `contains` |
| `priority` | unsignedInteger, default 0 | Higher = takes precedence |
| `is_active` | boolean, default true | Soft disable without deletion |
| `created_by` | foreignId, nullable | |
| `timestamps` | — | |

**Unique index:** `(chave_normalized, match_type)`

**Enum `MatchType`:** `Exact`, `TickerPrefix`, `Contains`

**Relationships:**
- `belongsTo(User::class, 'created_by')`

### 4.6 ProcessingEvent

Status audit trail. Polymorphic — attaches to Submission or Document.

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `eventable_type` | string | Polymorphic type |
| `eventable_id` | uuid | Polymorphic ID |
| `trace_id` | uuid | For end-to-end debugging |
| `status_from` | string, nullable | Previous status |
| `status_to` | string | New status |
| `event_type` | string | e.g., `status_change`, `extraction_started` |
| `metadata` | json, nullable | Extra context (error details, model, tokens) |
| `triggered_by` | string | `system`, `user`, `queue` |
| `created_at` | timestamp | Immutable — no `updated_at` |

**Relationships:**
- `morphTo('eventable')` — Submission or Document

### 4.7 AuditLog

System-wide audit for user actions.

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `user_id` | foreignId, nullable | Null for system actions |
| `action` | string | `upload`, `download`, `review`, `classify`, `rule_create`, etc. |
| `auditable_type` | string, nullable | Polymorphic |
| `auditable_id` | string, nullable | Polymorphic |
| `description` | text, nullable | |
| `metadata` | json, nullable | IP, user agent, details |
| `ip_address` | string(45), nullable | |
| `created_at` | timestamp | Immutable |

---

## 5. Document Status Machine

```
UPLOADED
  │ (ExtractDocumentJob dispatched)
  ▼
EXTRACTING
  │ success                        ╲ failure
  ▼                                 ▼
EXTRACTED                     EXTRACTION_FAILED (retryable)
  │ (ClassifyAssetsJob dispatched)
  ▼
CLASSIFYING
  │ success                        ╲ failure
  ▼                                 ▼
CLASSIFIED                    CLASSIFICATION_FAILED (retryable)
  │ (automatic transition)
  ▼
READY_FOR_REVIEW
  │ (analyst action)
  ▼
REVIEWED
  │ (analyst/admin action)
  ▼
APPROVED (terminal)
```

**Enum `DocumentStatus`:** `Uploaded`, `Extracting`, `Extracted`, `ExtractionFailed`, `Classifying`, `Classified`, `ClassificationFailed`, `ReadyForReview`, `Reviewed`, `Approved`

### Transition Rules

| From | To | Trigger |
|---|---|---|
| `Uploaded` | `Extracting` | ExtractDocumentJob starts |
| `Extracting` | `Extracted` | Extraction succeeds |
| `Extracting` | `ExtractionFailed` | Extraction fails after retries |
| `Extracted` | `Classifying` | ClassifyAssetsJob starts |
| `Classifying` | `Classified` | Classification succeeds |
| `Classifying` | `ClassificationFailed` | Classification fails after retries |
| `Classified` | `ReadyForReview` | Automatic (immediate) |
| `ReadyForReview` | `Reviewed` | Analyst marks as reviewed |
| `Reviewed` | `Approved` | Analyst/admin approves |
| `ExtractionFailed` | `Extracting` | Manual retry |
| `ClassificationFailed` | `Classifying` | Manual retry |

### Transition Side Effects

Every transition:
1. Creates a `ProcessingEvent` record
2. Broadcasts a Reverb event on channel `submission.{submissionId}` (from Phase 4)
3. Updates parent Submission cached counters and derived status

### Submission Status Derivation

| Submission Status | Condition |
|---|---|
| `Pending` | All documents are `Uploaded` |
| `Processing` | Any document is `Extracting` or `Classifying` |
| `PartiallyComplete` | Some documents completed, some failed |
| `Completed` | All documents are `Approved` |
| `Failed` | All documents are in a `*Failed` state |

**Service:** `App\Services\DocumentStatusMachine` — enforces valid transitions and fires side effects.

---

## 6. AI Agent Architecture (Laravel AI SDK)

### 6.1 ExtractionAgent

**File:** `app/AI/Agents/ExtractionAgent.php`

Implements `Agent`, `HasStructuredOutput`. Accepts document content (multimodal: image + text, or text-only) and returns structured asset positions.

**System Prompt:** Faithful port of `EXTRACTION_PROMPT` from the Python project (`portifolio-analysis-python/files/prompts.py`):
- Extract ONLY visible asset positions from the document
- Each item must have an identifiable asset name AND a gross value
- Return JSON array of `{Ativo, Posicao}` pairs
- Do NOT classify — extraction only
- Brazilian number format for Posição (e.g., `59.000,00`)
- Enrich asset names with issuer/rate/maturity when visible in the document

**Structured Output Schema:**

```
type: array
items:
  type: object
  properties:
    ativo:
      type: string
      description: "Asset name or ticker"
    posicao:
      type: string
      description: "Position value in Brazilian format (e.g., 59000,00)"
  required: [ativo, posicao]
```

**Extraction Strategy (per file type):**

| File Type | Strategy |
|---|---|
| Images (PNG, JPG, JPEG) | AI multimodal — send base64 image |
| PDF with extractable text | AI text — extract text with PHP PDF parser, send to AI |
| PDF scanned/image-only | AI multimodal — convert pages to images, send to vision |
| CSV | PHP parser (PhpSpreadsheet) first, AI text fallback |
| Excel (XLSX, XLS) | PHP parser (PhpSpreadsheet) first, AI text fallback |

**Retry:** Up to 2 retries on empty result with exponential backoff (`0.6 * 1.6^attempt` seconds, capped at 3s).

### 6.2 ClassificationAgent

**File:** `app/AI/Agents/ClassificationAgent.php`

Implements `Agent`, `HasStructuredOutput`. Accepts a text listing of `Ativo; Posicao` pairs and returns classifications.

**System Prompt:** Faithful port of `CLASSIFICATION_PROMPT` from the Python project:
- Classify each asset into exactly one Classe and one Estratégia from allowed sets
- Return JSON array with `{Classe, Estrategia, Confidence}`
- Include deterministic rule hints in the prompt to guide the model

**Structured Output Schema:**

```
type: array
items:
  type: object
  properties:
    classe:
      type: string
      enum: [allowed Classe values — see Section 6.4]
    estrategia:
      type: string
      enum: [allowed Estratégia values — see Section 6.4]
    confidence:
      type: number
      minimum: 0
      maximum: 1
  required: [classe, estrategia, confidence]
```

**Important:** The ClassificationAgent is the THIRD tier — called only for assets not resolved by Base1 lookup or deterministic rules.

### 6.3 ClassificationService (Orchestrator)

**File:** `app/Services/ClassificationService.php`

Applies 3-tier classification in priority order:

1. **Base1 DB lookup** — Query `ClassificationRule` table by `chave_normalized`. Exact match first, then ticker prefix match.
2. **Deterministic rules** — `App\Services\DeterministicClassifier` — PHP port of Python's `classificar_local` regex logic (see Section 6.5).
3. **AI classification** — `ClassificationAgent` for remaining unclassified assets.

Each asset records its `classification_source` (`base1`, `deterministic`, `ai`) for transparency.

### 6.4 Allowed Classification Labels

**Classe** (exactly one):

```
Ações, BDR's, Caixa/Conta Corrente, COE, CRI/CRA, Criptoativos, Debêntures,
Derivativos, Emissão Bancária (CDB, LCI/LCA), ETF's, Fundos de Investimentos,
Fundos Imobiliários, Outros, Poupança, Stocks, Título Público
```

**Estratégia** (exactly one):

```
Ações Americanas, Ações Brasil, Caixa, Criptoativos, Fundos Imobiliários,
Multimercado, Outros, Renda Fixa, Renda Fixa Inflação, Renda Fixa Pós Fixada,
Renda Fixa Pré Fixada, Previdência
```

### 6.5 DeterministicClassifier

**File:** `app/Services/DeterministicClassifier.php`

PHP port of Python's `classificar_local` function (~370 lines of regex logic). Key classification rules:

| Pattern | Classe | Estratégia |
|---|---|---|
| `SALDO.*DISPON`, `CONTA CORRENTE`, `CAIXA` | Caixa/Conta Corrente | Caixa |
| `POUPANÇA` | Poupança | Renda Fixa Pós Fixada |
| `PREV`, `PGBL`, `VGBL` | Fundos de Investimentos | Previdência |
| `COE`, autocall/bidirecional terms | COE | Outros |
| FII regex (`[A-Z]{3,6}11`) | Fundos Imobiliários | Fundos Imobiliários |
| `CDB`, `LCI`, `LCA` + rate detection | Emissão Bancária | Renda Fixa Pós/Inflação/Pré |
| `TESOURO SELIC`, `LFT` | Título Público | Renda Fixa Pós Fixada |
| `TESOURO IPCA`, `NTN-B` | Título Público | Renda Fixa Inflação |
| `TESOURO PREFIX`, `LTN` | Título Público | Renda Fixa Pré Fixada |
| Debênture patterns | Debêntures | Rate-dependent |
| `CRI`, `CRA` patterns | CRI/CRA | Rate-dependent |
| Fund patterns (FIC, FIM, FIA, etc.) | Fundos de Investimentos | Mandate-dependent |
| BDR ticker (`[A-Z0-9]{4}34`) | BDR's | Ações Americanas |
| B3 stock ticker (`[A-Z0-9]{4}[3-7]`) | Ações | Ações Brasil |
| US stock ticker (`[A-Z]{1,5}`) | Stocks | Ações Americanas |
| Known crypto tickers (BTC, ETH, SOL, etc.) | Criptoativos | Criptoativos |

The classifier also includes a label validation step (`impor_rotulos_permitidos`) that corrects common AI misclassifications and enforces allowed label sets.

### 6.6 AI Configuration

**File:** `config/ai.php` (published by `laravel/ai`)

```php
'providers' => [
    'openai' => [
        'driver' => 'openai',
        'key' => env('OPENAI_API_KEY'),
        'url' => env('OPENAI_BASE_URL'),
    ],
    'anthropic' => [
        'driver' => 'anthropic',
        'key' => env('ANTHROPIC_API_KEY'),
        'url' => env('ANTHROPIC_BASE_URL'),
    ],
],
```

Custom config values in `config/portfolio.php`:

```php
return [
    'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
        'extraction_model' => env('AI_EXTRACTION_MODEL', 'gpt-4.1'),
        'classification_model' => env('AI_CLASSIFICATION_MODEL', 'gpt-4.1'),
        'max_retries' => (int) env('AI_MAX_RETRIES', 2),
        'retry_delay_ms' => (int) env('AI_RETRY_DELAY', 600),
        'classification_batch_size' => (int) env('AI_CLASSIFICATION_BATCH_SIZE', 50),
    ],
    'upload' => [
        'max_file_size_mb' => (int) env('UPLOAD_MAX_FILE_SIZE_MB', 50),
        'max_files_per_submission' => (int) env('UPLOAD_MAX_FILES_PER_SUBMISSION', 20),
        'accepted_extensions' => ['pdf', 'png', 'jpg', 'jpeg', 'csv', 'xlsx', 'xls'],
    ],
];
```

---

## 7. Processing Pipeline

### Complete Async Flow

```
1. User uploads files via SubmissionController@store
   ├── Files saved to storage/app/private/submissions/{submission_id}/
   ├── Submission record created (status: Pending)
   ├── Document records created (status: Uploaded)
   └── ProcessSubmissionJob dispatched to queue

2. ProcessSubmissionJob executes
   ├── Submission status → Processing
   ├── For each Document:
   │   └── ExtractDocumentJob dispatched (chained with ClassifyAssetsJob)
   └── Broadcasts SubmissionStatusChanged (Phase 4+)

3. ExtractDocumentJob executes (per document)
   ├── Document status: Uploaded → Extracting
   ├── Determine extraction strategy:
   │   ├── PDF (text-extractable) → PHP text extraction → AI text prompt
   │   ├── PDF (scanned/image) → Convert pages to images → AI multimodal
   │   ├── Images (.png, .jpg) → AI multimodal (base64)
   │   ├── CSV → PhpSpreadsheet parser → AI text fallback
   │   └── Excel → PhpSpreadsheet parser → AI text fallback
   ├── Call ExtractionAgent (or PHP parser)
   ├── Normalize: ativo → ticker, posicao → numeric
   ├── Create ExtractedAsset records
   ├── Document status: Extracting → Extracted
   └── On failure: status → ExtractionFailed, log error

4. ClassifyAssetsJob executes (chained, per document)
   ├── Document status: Extracted → Classifying
   ├── For each ExtractedAsset:
   │   ├── Tier 1: ClassificationRule DB lookup (chave_normalized)
   │   │   └── Match → set classe, estrategia, source='base1'
   │   ├── Tier 2: DeterministicClassifier (regex rules)
   │   │   └── Match → set classe, estrategia, source='deterministic'
   │   └── Tier 3: ClassificationAgent (AI)
   │       └── Set classe, estrategia, confidence, source='ai'
   ├── Run label validation (enforce allowed sets)
   ├── Document status: Classifying → Classified → ReadyForReview
   └── On failure: status → ClassificationFailed

5. Submission status updated based on aggregate document states
   └── Broadcasts SubmissionStatusChanged (Phase 4+)

6. Analyst reviews in UI
   ├── Corrects classifications if needed (source='manual')
   ├── Document status: ReadyForReview → Reviewed → Approved
   └── Submission status: Completed (when all documents approved)
```

### Queue Configuration

| Queue Name | Jobs | Timeout | Retries |
|---|---|---|---|
| `default` | ProcessSubmissionJob | 30s | 3 |
| `extraction` | ExtractDocumentJob | 300s (5 min) | 3 |
| `classification` | ClassifyAssetsJob | 120s | 3 |

Worker command: `php artisan queue:work --queue=extraction,classification,default --tries=3 --timeout=300`

### Normalization Functions

Port from Python project:
- `normalizar_texto(string)` → uppercase, whitespace-compressed
- `extrair_ticker_b3(string)` → extract B3 ticker pattern `[A-Z]{4}[0-9]{1,2}`
- `normalizar_posicoes(string)` → parse Brazilian number format to decimal

---

## 8. Frontend Architecture

### 8.1 Inertia + React Patterns

- Server-side routing via `Inertia::render()` — no client-side router
- `useForm` from `@inertiajs/react` for all form submissions
- Wayfinder-generated route functions from `@/actions/` and `@/routes/`
- Deferred props via `Inertia::defer()` for heavy data loads
- `useHttp` for standalone HTTP requests (classification options, exports)
- TypeScript interfaces for all page props

### 8.2 Page Inventory

```
resources/js/pages/
├── auth/                          (EXISTING — Fortify)
│   ├── login.tsx
│   ├── register.tsx
│   ├── forgot-password.tsx
│   ├── reset-password.tsx
│   ├── verify-email.tsx
│   ├── confirm-password.tsx
│   └── two-factor-challenge.tsx
├── dashboard.tsx                  (EXISTING — enhanced with stats)
├── submissions/
│   ├── index.tsx                  # Paginated list with status filters
│   ├── create.tsx                 # Multi-file upload with drag-and-drop
│   ├── show.tsx                   # Submission detail + document list + live status
│   └── portfolio.tsx              # Aggregated portfolio summary + charts + export
├── documents/
│   └── show.tsx                   # Document detail + extracted assets table
├── classification-rules/
│   ├── index.tsx                  # Searchable table + inline edit (admin)
│   ├── create.tsx                 # New rule form (admin)
│   └── edit.tsx                   # Edit rule form (admin)
├── users/
│   ├── index.tsx                  # User list with roles (admin)
│   ├── create.tsx                 # Invite user form (admin)
│   └── edit.tsx                   # Edit user/role (admin)
├── admin/
│   └── dashboard.tsx              # Queue health + stats + audit log (admin)
├── settings/                      (EXISTING)
│   ├── profile.tsx
│   ├��─ security.tsx
│   └── appearance.tsx
└── welcome.tsx                    (EXISTING)
```

### 8.3 Key React Components

```
resources/js/components/
├── submissions/
│   ├── upload-dropzone.tsx        # Drag-and-drop file upload zone
│   ├── upload-progress.tsx        # Per-file upload progress bars
│   ├── submission-status-badge.tsx
│   ├── submission-table.tsx       # Data table for submission list
│   └── document-card.tsx          # Card for document in submission detail
├── documents/
│   ├── document-status-badge.tsx
│   └── asset-review-table.tsx     # Editable table for asset review
├── portfolio/
│   ├── allocation-chart.tsx       # Pie/donut chart by estratégia
│   ├── portfolio-summary-table.tsx # Grouped totals by classe/estratégia
│   └── export-button.tsx          # CSV/Excel download
├── classification-rules/
│   ├── rule-table.tsx             # Searchable rule list
│   └── rule-form.tsx              # Create/edit rule form
├── admin/
│   ├── queue-health-card.tsx
│   ├── processing-stats-card.tsx
│   └── audit-log-table.tsx
└── ui/                            (EXISTING — Radix primitives)
```

### 8.4 Key React Hooks

```
resources/js/hooks/
├── use-submission-channel.ts      # Reverb subscription for submission updates
├── use-document-status.ts         # Reactive document status from Reverb
└── use-classification-options.ts  # Fetch allowed Classe/Estratégia sets
```

### 8.5 TypeScript Types

```
resources/js/types/
├── submission.ts                  # Submission, SubmissionStatus
├── document.ts                    # Document, DocumentStatus
├── extracted-asset.ts             # ExtractedAsset, ClassificationSource
├── classification-rule.ts         # ClassificationRule
├── processing-event.ts            # ProcessingEvent
└── portfolio.ts                   # PortfolioGroup, PortfolioSummary
```

---

## 9. Phase Plan

> **Instruction for AI planners:** Each phase must be fully functional and deployable on its own. No phase should depend on future phases to work. Each phase builds on the previous one.

### Phase 1 — Foundation: Auth, Roles, and Core Models (3 PRs)

**Goal:** Extend the existing Fortify auth with role-based access. Create all core domain migrations, models, factories, and seeders.

**Deliverables:**

- [ ] `UserRole` enum with `Admin`, `Analyst`, `Viewer` cases
- [ ] `role` column added to `users` table
- [ ] `UserPolicy` for role-based gates
- [ ] Updated `UserFactory` with `->asAdmin()`, `->asAnalyst()`, `->asViewer()` states
- [ ] Migrations for all 6 domain tables (submissions, documents, extracted_assets, classification_rules, processing_events, audit_logs)
- [ ] All Eloquent models with relationships, casts, fillable attributes
- [ ] All factories with realistic Brazilian financial data
- [ ] All enums: `UserRole`, `SubmissionStatus`, `DocumentStatus`, `ClassificationSource`, `MatchType`
- [ ] `ClassificationRuleSeeder` importing `base1.csv` (shipped as `database/data/base1.csv`)
- [ ] Updated sidebar navigation with role-gated links
- [ ] Placeholder Inertia pages for submissions, classification-rules, users
- [ ] Route registration with role-based middleware
- [ ] `HandleInertiaRequests` sharing `auth.user.role`
- [ ] Pest tests for role access, model relationships, arch tests

**Validation:** User logs in, sees role-appropriate sidebar. Admin sees all links. Viewer sees only Dashboard and Submissions. Models exist with correct relationships.

---

### Phase 2 — File Upload and Document Management (3 PRs)

**Goal:** Users can upload portfolio documents. Files stored on local disk. Documents appear in submission history.

**Deliverables:**

- [ ] `SubmissionController` with `create`, `store`, `index`, `show`
- [ ] `DocumentController` with `show`, `download`
- [ ] `StoreSubmissionRequest` form request (file validation: mime types, max size)
- [ ] `DocumentStorageService` — stores files under `storage/app/private/submissions/{id}/`
- [ ] Multi-file drag-and-drop upload component (`upload-dropzone.tsx`)
- [ ] Upload progress indicator per file
- [ ] Submission history page with filters (status, date range)
- [ ] Submission detail page with document list cards
- [ ] Document detail page with metadata and download
- [ ] Wayfinder route generation for new controllers
- [ ] Pest tests: upload flow, validation, authorization, download

**Validation:** User uploads 5 files. Submission appears in history with status `Pending`. Each document shows as `Uploaded`. Files can be downloaded. Viewer can see submissions but cannot create.

---

### Phase 3 — AI Extraction Pipeline (4 PRs)

**Goal:** Documents are processed by AI to extract asset positions. Queue-based async processing.

**Deliverables:**

- [ ] `laravel/ai` package installed and configured
- [ ] `config/ai.php` with provider configuration
- [ ] `config/portfolio.php` with extraction/classification settings
- [ ] `ExtractionAgent` — multimodal agent with structured output
- [ ] PHP fallback parsers for CSV/Excel (PhpSpreadsheet)
- [ ] `ExtractDocumentJob` — queue job with retry logic
- [ ] `ClassifyAssetsJob` — queue job chained after extraction
- [ ] `ClassificationService` — 3-tier orchestrator (Base1 → Deterministic → AI)
- [ ] `DeterministicClassifier` — PHP port of Python regex rules
- [ ] `ClassificationAgent` — structured output for unresolved assets
- [ ] `DocumentStatusMachine` — enforces valid transitions
- [ ] `ProcessSubmissionJob` — orchestrates per-document fan-out
- [ ] `ProcessingEvent` creation at each transition
- [ ] Asset normalization functions (ticker extraction, position parsing)
- [ ] Artisan command: `portfolio:reprocess {submission}` for retry
- [ ] Pest tests: full pipeline with mocked AI, partial failures, classification tiers

**Validation:** Upload 3 documents. Queue worker processes them. Assets appear with classifications. CSV classified from Base1/deterministic. PDF triggers AI extraction. Failed document shows `ExtractionFailed`.

---

### Phase 4 — Real-Time Updates with Reverb (2 PRs)

**Goal:** Users see live processing status without page refreshes.

**Deliverables:**

- [ ] `laravel/reverb` installed and configured
- [ ] `laravel-echo` and `pusher-js` npm packages installed
- [ ] Broadcast events: `DocumentStatusChanged`, `SubmissionStatusChanged`
- [ ] Channel authorization in `routes/channels.php`
- [ ] Events integrated into `DocumentStatusMachine`
- [ ] Echo configuration in `resources/js/echo.ts`
- [ ] `useSubmissionChannel` React hook for live updates
- [ ] `submissions/show.tsx` updated with live document status
- [ ] Animated status badges on Reverb events
- [ ] Toast notifications for key events (complete, failed)
- [ ] Dashboard stat cards with live counters
- [ ] Updated `composer run dev` to include Reverb
- [ ] Pest tests: events broadcast on status transitions

**Validation:** Open submission detail. Upload documents in another tab. Watch statuses update live. Toast appears when processing completes.

---

### Phase 5 — Classification Review UI (3 PRs)

**Goal:** Analysts review and correct AI classifications. Admins manage classification rules.

**Deliverables:**

- [ ] `ClassificationRuleController` full CRUD
- [ ] Classification rules data table with search, filter, inline edit (admin-only)
- [ ] Create/edit rule forms with Classe/Estratégia dropdowns
- [ ] Bulk CSV import/export for classification rules
- [ ] `ExtractedAssetController@update` — analyst corrects classifications
- [ ] Asset review table with editable Classe/Estratégia dropdowns
- [ ] Color-coded classification source indicators (Base1=green, Deterministic=blue, AI=yellow, Manual=purple)
- [ ] "Approve All" batch action
- [ ] Review state tracking (`is_reviewed`, `reviewed_by`, `reviewed_at`)
- [ ] Portfolio summary view with grouping by Classe/Estratégia
- [ ] Allocation pie/donut chart
- [ ] CSV/Excel export of classified portfolio
- [ ] Pest tests: CRUD, review flow, authorization, CSV import

**Validation:** Admin imports updated CSV. Analyst reviews a submission, corrects two classifications. Portfolio summary reflects corrections. Export works.

---

### Phase 6 — Admin Dashboard and User Management (2 PRs)

**Goal:** Admin has full visibility into system health and user management.

**Deliverables:**

- [ ] `UserController` full CRUD (admin-only)
- [ ] User list with role badges, create, edit, deactivate
- [ ] Admin dashboard page with queue health (pending/failed job counts)
- [ ] Processing statistics: submissions per day, success rate, average time
- [ ] Recent ProcessingEvent timeline
- [ ] Audit log viewer with filtering
- [ ] Pest tests: admin access, data aggregation, user management

**Validation:** Admin sees queue stats, recent activity, manages users. Non-admin denied access.

---

### Phase 7 — Hardening, Testing, and Polish (3 PRs)

**Goal:** Production readiness. Comprehensive tests. Error handling. Performance.

**Deliverables:**

- [ ] Inertia error pages (403, 404, 500)
- [ ] AI circuit breaker: retry with delay, fall back to PHP parsers
- [ ] Large file rejection, empty extraction handling
- [ ] Rate limiting on upload endpoint
- [ ] Request validation hardening
- [ ] Architecture tests (model namespaces, no direct DB in controllers)
- [ ] Full pipeline integration test (upload → extract → classify → review → approve)
- [ ] Pest datasets for classification rules
- [ ] Complete `.env.example` with documentation
- [ ] Health check route (`/health`)
- [ ] `portfolio:seed-demo` command for demo data
- [ ] All tests pass, CI-ready

**Validation:** All tests pass. New developer can clone, seed, and run the app.

---

## 10. Environment Variables Reference

```env
# Application
APP_NAME="Portfolio Analysis"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# Database
DB_CONNECTION=sqlite

# Queue
QUEUE_CONNECTION=database

# Session / Cache
SESSION_DRIVER=database
CACHE_STORE=database

# AI Providers (Laravel AI SDK)
AI_PROVIDER=openai                              # openai | anthropic | gemini
OPENAI_API_KEY=                                  # Required if AI_PROVIDER=openai
ANTHROPIC_API_KEY=                               # Required if AI_PROVIDER=anthropic
GEMINI_API_KEY=                                  # Required if AI_PROVIDER=gemini

# AI Behavior
AI_EXTRACTION_MODEL=gpt-4.1                      # Model for document extraction
AI_CLASSIFICATION_MODEL=gpt-4.1                  # Model for asset classification
AI_MAX_RETRIES=2                                 # Retries on empty result
AI_RETRY_DELAY=600                               # Base delay in ms
AI_CLASSIFICATION_BATCH_SIZE=50                  # Max assets per AI call

# File Upload Limits
UPLOAD_MAX_FILE_SIZE_MB=50
UPLOAD_MAX_FILES_PER_SUBMISSION=20

# Broadcasting (Reverb — Phase 4)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=portfolio-local
REVERB_APP_KEY=portfolio-key
REVERB_APP_SECRET=portfolio-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# Mail (dev)
MAIL_MAILER=log
```

---

## 11. AI Planning Rules

> These rules govern agent-assisted development of this project. Follow them exactly.

1. **Respect phase boundaries.** Do not introduce code or dependencies from a later phase. Each phase must stand alone.
2. **Confirm previous phase is complete** before starting a new one. Run all checks.
3. **Follow the document status machine exactly.** No skipping states. Every transition fires a ProcessingEvent.
4. **3-tier classification priority is sacred.** Base1 DB → Deterministic rules → AI. Never skip tiers.
5. **Queue jobs must be idempotent.** Re-dispatching must not create duplicate ExtractedAsset records.
6. **Write Pest tests with every PR.** Use `RefreshDatabase`. Use factories with realistic Brazilian financial data.
7. **Use Laravel conventions.** Form Requests for validation, Policies for authorization, Jobs for async work, Events + Listeners for domain events.
8. **Use React 19 + TypeScript + Inertia v3 patterns.** `useForm` for forms, Wayfinder for routes, deferred props for heavy data, `useHttp` for standalone requests.
9. **Port Python logic faithfully but idiomatically.** Same regex patterns, PHP conventions. Use classes, services, enums.
10. **Never hardcode AI API keys.** Environment variables exclusively.
11. **Test AI with mocks.** Never call real AI APIs in tests. Create mock responses matching structured output schemas.
12. **Brazilian Portuguese in domain fields, English in code.** Domain terms: `ativo`, `posicao`, `classe`, `estrategia`. Code: English class names, methods, variables.
13. **Run formatting after every change.** `vendor/bin/pint --dirty --format agent` for PHP. `npm run lint` and `npm run types:check` for TypeScript.
14. **Always use `search-docs` before making code changes.** Consult the Laravel Boost documentation API for version-specific guidance.
15. **Activate relevant skills.** `laravel-best-practices` for PHP, `pest-testing` for tests, `inertia-react-development` for frontend, `tailwindcss-development` for styling, `wayfinder-development` for route generation.

---

## 12. Glossary

| Term | Definition |
|---|---|
| **Ativo** | An asset/investment position (stock, bond, fund, etc.) |
| **Posição** | The financial position (value) of an asset, in Brazilian Real format |
| **Classe** | Asset class category (e.g., Ações, Fundos Imobiliários, ETF's, Título Público) |
| **Estratégia** | Investment strategy sub-category (e.g., Ações Brasil, Renda Fixa Pós Fixada) |
| **Base1** | The reference database mapping known assets to their Classe/Estratégia |
| **Submission** | A batch upload of one or more documents by a user |
| **Document** | A single file (PDF, image, spreadsheet) within a submission |
| **ExtractedAsset** | An individual asset position extracted from a document |
| **ClassificationRule** | A row in the Base1 reference table |
| **ProcessingEvent** | An audit trail entry recording a status change |
| **Ticker** | A B3 stock exchange symbol (e.g., PETR4, VALE3, KNCR11) |
| **FII** | Fundo de Investimento Imobiliário (Real Estate Investment Trust) |
| **BDR** | Brazilian Depositary Receipt |
| **COE** | Certificado de Operações Estruturadas (Structured Operations Certificate) |
| **CRI/CRA** | Certificados de Recebíveis Imobiliários/do Agronegócio |
| **Emissão Bancária** | Bank-issued fixed income (CDB, LCI, LCA) |
| **Título Público** | Government bonds (Tesouro Direto) |
| **DeterministicClassifier** | PHP service using regex rules to classify assets without AI |
| **ExtractionAgent** | Laravel AI SDK agent for extracting asset data from documents |
| **ClassificationAgent** | Laravel AI SDK agent for classifying assets into Classe/Estratégia |
| **Reverb** | Laravel's first-party WebSocket server |
| **Wayfinder** | Laravel package generating typed TypeScript route functions |
| **Ralph Loop** | Autonomous continue loop for AI-assisted incremental development |

---

## 13. Critical Source Files for Porting

These Python files contain the logic that must be faithfully ported to PHP:

| Python File | Purpose | Laravel Target |
|---|---|---|
| `files/prompts.py` | `EXTRACTION_PROMPT`, `CLASSIFICATION_PROMPT` | ExtractionAgent instructions, ClassificationAgent instructions |
| `classification.py` → `classificar_local()` | ~370 lines of deterministic regex rules | `App\Services\DeterministicClassifier` |
| `classification.py` → `buscar_na_base1()` | Base1 CSV lookup with normalization | `ClassificationService` → DB query |
| `classification.py` → `impor_rotulos_permitidos()` | Label validation and correction | `ClassificationService` → `validateLabels()` |
| `files/extractor.py` | Multimodal extraction with retry logic | `ExtractionAgent` + `ExtractDocumentJob` |
| `processor.py` | Pipeline orchestration | `ProcessSubmissionJob` + job chaining |
| `data/base1.csv` | Classification reference (~thousands of rows) | `database/data/base1.csv` → `ClassificationRuleSeeder` |
