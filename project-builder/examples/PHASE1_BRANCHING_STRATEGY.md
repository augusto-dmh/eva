# Phase 1 — Branching Strategy

## Phase Goal

Extend the existing Fortify auth with role-based access. Create all core domain migrations, models, factories, enums, and seeders. Set up base navigation with role-gated sidebar.

## PR Order and Dependencies

```
PR 1.1: feat/user-roles-authorization
  └── branches from: master

PR 1.2: feat/core-domain-models
  └── branches from: master (after PR 1.1 merged)

PR 1.3: feat/base-navigation-sidebar
  └── branches from: master (after PR 1.2 merged)
```

## Branch Names

| PR | Branch | Description |
|---|---|---|
| 1.1 | `feat/user-roles-authorization` | UserRole enum, migration, policy, factory states |
| 1.2 | `feat/core-domain-models` | All 6 domain tables, models, factories, enums, Base1 seeder |
| 1.3 | `feat/base-navigation-sidebar` | Sidebar navigation, placeholder pages, route registration |

## Merge Flow

Each PR merges directly into `master`:

```
master ──── PR 1.1 merge ──── PR 1.2 merge ──── PR 1.3 merge ────
```

## After Phase Complete

- All 3 PRs merged to `master`
- All checks pass on `master`
- Phase 2 branches from updated `master`
