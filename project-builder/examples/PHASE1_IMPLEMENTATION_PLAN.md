# Phase 1 — Implementation Plan

## Prerequisites

Before starting Phase 1, verify:

```bash
composer setup                    # Full project setup
php artisan test --compact        # Existing tests pass
npm run build                     # Frontend builds cleanly
```

---

## PR 1.1: User Roles and Authorization

**Branch:** `feat/user-roles-authorization`

### Steps

1. **Create UserRole enum**
   ```bash
   php artisan make:enum UserRole --no-interaction
   ```
   - Cases: `Admin = 'admin'`, `Analyst = 'analyst'`, `Viewer = 'viewer'`
   - String-backed enum

2. **Add role column to users table**
   ```bash
   php artisan make:migration add_role_to_users_table --table=users --no-interaction
   ```
   - Add `string('role')->default('viewer')` column after `password`
   - Update existing seeder/factory

3. **Update User model**
   - Add `role` to `$fillable` and `$casts` (cast to `UserRole` enum)
   - Add helper methods: `isAdmin(): bool`, `isAnalyst(): bool`, `isViewer(): bool`
   - Add relationship: `hasMany(Submission::class)` (forward declaration, model comes in PR 1.2)

4. **Update UserFactory**
   - Default role: `viewer`
   - Add states: `->asAdmin()`, `->asAnalyst()`, `->asViewer()`

5. **Create UserPolicy**
   ```bash
   php artisan make:policy UserPolicy --model=User --no-interaction
   ```
   - `viewAny`: admin only
   - `create`: admin only
   - `update`: admin only (prevent self-role-change)
   - `delete`: admin only (prevent self-delete)

6. **Register authorization**
   - Register `UserPolicy` in `AppServiceProvider`
   - Define Gate: `admin` → checks `UserRole::Admin`
   - Define Gate: `analyst-or-above` → checks Admin or Analyst
   - Define Gate: `viewer-or-above` → allows all authenticated

7. **Update HandleInertiaRequests**
   - Share `auth.user.role` in shared data

8. **Update TypeScript User type**
   - Add `role: 'admin' | 'analyst' | 'viewer'` to User interface in `resources/js/types/`

9. **Update DatabaseSeeder**
   - Create default admin user: `admin@portfolio.test` / `password`

10. **Write Pest tests**
    - Test role enum values
    - Test factory states produce correct roles
    - Test UserPolicy gates (admin access, non-admin denial)
    - Test HandleInertiaRequests shares role
    - Run `vendor/bin/pint --dirty --format agent`

### Commit Plan

1. `feat: add UserRole enum`
2. `feat: add role column to users table`
3. `feat: update User model with role cast and helpers`
4. `feat: add UserPolicy and authorization gates`
5. `feat: update Inertia shared data with user role`
6. `test: add role-based authorization tests`

---

## PR 1.2: Core Domain Migrations and Models

**Branch:** `feat/core-domain-models`

### Steps

1. **Create enums**
   - `SubmissionStatus`: `Pending`, `Processing`, `PartiallyComplete`, `Completed`, `Failed`
   - `DocumentStatus`: `Uploaded`, `Extracting`, `Extracted`, `ExtractionFailed`, `Classifying`, `Classified`, `ClassificationFailed`, `ReadyForReview`, `Reviewed`, `Approved`
   - `ClassificationSource`: `Base1`, `Deterministic`, `Ai`, `Manual`
   - `MatchType`: `Exact`, `TickerPrefix`, `Contains`

2. **Create migrations** (in dependency order)
   ```bash
   php artisan make:migration create_submissions_table --no-interaction
   php artisan make:migration create_documents_table --no-interaction
   php artisan make:migration create_extracted_assets_table --no-interaction
   php artisan make:migration create_classification_rules_table --no-interaction
   php artisan make:migration create_processing_events_table --no-interaction
   php artisan make:migration create_audit_logs_table --no-interaction
   ```
   - Follow field definitions from Project Specification Section 4

3. **Create models**
   ```bash
   php artisan make:model Submission --no-interaction
   php artisan make:model Document --no-interaction
   php artisan make:model ExtractedAsset --no-interaction
   php artisan make:model ClassificationRule --no-interaction
   php artisan make:model ProcessingEvent --no-interaction
   php artisan make:model AuditLog --no-interaction
   ```
   - Add all relationships, casts, fillable, uuid traits as needed
   - Submission and Document use UUID primary keys

4. **Create factories**
   ```bash
   php artisan make:factory SubmissionFactory --model=Submission --no-interaction
   php artisan make:factory DocumentFactory --model=Document --no-interaction
   php artisan make:factory ExtractedAssetFactory --model=ExtractedAsset --no-interaction
   php artisan make:factory ClassificationRuleFactory --model=ClassificationRule --no-interaction
   php artisan make:factory ProcessingEventFactory --model=ProcessingEvent --no-interaction
   php artisan make:factory AuditLogFactory --model=AuditLog --no-interaction
   ```
   - Use realistic Brazilian financial data (real ticker names, Portuguese asset descriptions)
   - Factory states for different statuses

5. **Copy Base1 CSV to project**
   - Copy `../portifolio-analysis-python/data/base1.csv` to `database/data/base1.csv`

6. **Create ClassificationRuleSeeder**
   ```bash
   php artisan make:seeder ClassificationRuleSeeder --no-interaction
   ```
   - Read `database/data/base1.csv`
   - Create `ClassificationRule` records with normalized chave
   - Skip duplicates

7. **Write Pest tests**
   - Model relationship tests (belongsTo, hasMany, morphMany)
   - Factory create tests
   - Enum value tests
   - Architecture tests: models in `App\Models`, enums in `App\Enums`
   - Run `vendor/bin/pint --dirty --format agent`

### Commit Plan

1. `feat: add domain enums (SubmissionStatus, DocumentStatus, ClassificationSource, MatchType)`
2. `feat: add submissions and documents migrations`
3. `feat: add extracted_assets and classification_rules migrations`
4. `feat: add processing_events and audit_logs migrations`
5. `feat: add Submission and Document models with relationships`
6. `feat: add ExtractedAsset and ClassificationRule models`
7. `feat: add ProcessingEvent and AuditLog models`
8. `feat: add model factories with Brazilian financial data`
9. `feat: add ClassificationRuleSeeder with Base1 CSV import`
10. `test: add model relationship and architecture tests`

---

## PR 1.3: Base Navigation and Role-Gated Sidebar

**Branch:** `feat/base-navigation-sidebar`

### Steps

1. **Create controllers**
   ```bash
   php artisan make:controller SubmissionController --no-interaction
   php artisan make:controller ClassificationRuleController --no-interaction
   php artisan make:controller UserController --no-interaction
   ```
   - Each with `index` method only (returns Inertia placeholder page)

2. **Register routes** in `routes/web.php`
   ```php
   // All authenticated + verified users
   Route::middleware(['auth', 'verified'])->group(function () {
       Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
       
       // Admin only
       Route::middleware('can:admin')->group(function () {
           Route::get('/classification-rules', [ClassificationRuleController::class, 'index'])->name('classification-rules.index');
           Route::get('/users', [UserController::class, 'index'])->name('users.index');
       });
   });
   ```

3. **Create placeholder Inertia pages**
   - `resources/js/pages/submissions/index.tsx` — "Submissions" heading, empty state
   - `resources/js/pages/classification-rules/index.tsx` — "Classification Rules" heading (admin)
   - `resources/js/pages/users/index.tsx` — "Users" heading (admin)

4. **Update sidebar navigation** (`resources/js/components/app-sidebar.tsx`)
   - Add navigation items: Dashboard, Submissions, Classification Rules (admin), Users (admin)
   - Use `usePage().props.auth.user.role` to conditionally show admin links
   - Use Lucide icons for each nav item

5. **Generate Wayfinder routes**
   ```bash
   php artisan wayfinder:generate
   ```

6. **Write Pest tests**
   - Test each route accessible by correct role
   - Test admin-only routes denied to analyst and viewer
   - Test sidebar renders correct links per role
   - Run `vendor/bin/pint --dirty --format agent`
   - Run `npm run lint && npm run types:check && npm run build`

### Commit Plan

1. `feat: add placeholder controllers for submissions, rules, and users`
2. `feat: register routes with role-based middleware`
3. `feat: add placeholder Inertia pages`
4. `feat: update sidebar navigation with role-gated links`
5. `test: add route access authorization tests`
