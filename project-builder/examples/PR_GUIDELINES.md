# PR Guidelines

## Branch Naming

Use conventional prefixes that describe the feature, not the phase:

- `feat/` — new feature or capability
- `fix/` — bug fix
- `docs/` — documentation only
- `chore/` — tooling, config, dependencies

Examples:
- `feat/user-roles-authorization` ✓
- `feat/core-domain-models` ✓
- `phase-1-pr-1` ✗

## PR Title

- Conventional commit style: `feat: ...`, `fix: ...`, `docs: ...`, `chore: ...`
- Under 70 characters
- Imperative mood: "Add", not "Added"
- Describe the feature, NOT the phase

Examples:
- `feat: add role-based user authorization` ✓
- `feat: add core domain models and factories` ✓
- `Phase 1 PR 1: roles` ✗

## PR Description

Use this template:

```markdown
## Why
[motivation and context — why this change matters, what it unblocks]

## What changed
[summarized behavioral changes, grouped logically — new models, endpoints, pages, tests]
```

Rules:
- NO mention of phases, PRDs, PROGRESS files, or planning artifacts
- Describe delivered capability, not planning steps
- Use headers, bullets, numbered lists for scannability
- Keep it concise but complete

## Commit Messages

- Conventional commit style: `feat: ...`, `fix: ...`, `test: ...`, `chore: ...`
- One logical unit per commit
- Imperative mood
- Review/fix commits are separate, never squash

Examples:
- `feat: add UserRole enum and migration`
- `feat: add Submission and Document models with factories`
- `test: add role-based access tests`
- `fix: correct classification rule unique constraint`

## Required Checks Before Merge

Run all of these before opening or updating a PR:

```bash
# PHP
vendor/bin/pint --dirty --format agent       # Code formatting
php artisan test --compact                    # Backend tests

# Frontend
npm run lint                                  # ESLint
npm run format:check                          # Prettier
npm run types:check                           # TypeScript
npm run build                                 # Production build
```

## Self-Review Checklist

Before marking a PR as ready:

- [ ] All tests pass
- [ ] No `dd()`, `dump()`, or `console.log` left in code
- [ ] Factory states created for all new models
- [ ] Form Requests validate all user input
- [ ] Policies enforce authorization on all controller actions
- [ ] New routes registered and Wayfinder regenerated (`php artisan wayfinder:generate`)
- [ ] TypeScript types updated for new page props
- [ ] No unrelated changes included
