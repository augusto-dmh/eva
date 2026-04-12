# CONTINUE_PROJECT_CODEX

When I say `continue`, you should autonomously continue the project by shipping the next logical PR from the current project queue and repository state.

## Primary Goal

Considering the last merged PRs, the current branch state, and the active project specifications, develop the next PR that continues the project defined in:

- [Portfolio_Analysis_Project_Specification.md](Portfolio_Analysis_Project_Specification.md)

## Required Reading Order

Before planning or implementing, always read and follow:

1. [CLAUDE.md](CLAUDE.md)
2. [PR_GUIDELINES.md](PR_GUIDELINES.md)
3. The active phase files such as:
   - `PHASE{N}_BRANCHING_STRATEGY.md`
   - `PHASE{N}_IMPLEMENTATION_PLAN.md`
4. [Portfolio_Analysis_Project_Specification.md](Portfolio_Analysis_Project_Specification.md)

## Decision Priority

When deciding what to build next, use this order:

1. Current repository state
2. Active project specifications
3. Active phase branching and implementation files
4. Merged PR and commit history on the current repository
5. Local-only PRD and PROGRESS files

## Autonomous Continue Loop

When I say `continue`, you should do all of the following without asking for confirmation unless blocked:

1. Inspect the current branch, recent merged PRs, open PRs, and recent commits.
2. Infer the next logical PR based on the active project phase and repository history.
3. Create or update a local-only PRD file for that PR.
4. Create or update a local-only PROGRESS file for that PR.
5. Create a focused feature branch.
6. Implement the PR end to end.
7. Add or update the required automated tests.
8. Run the required formatting, linting, tests, and build steps.
9. Commit the work in logical sequential commits as if a human were doing the work.
10. Push the branch.
11. Open or update the GitHub PR with the correct title and body.
12. Check GitHub CI and PR status.
13. If CI fails, fix the failures and push again.
14. If repository settings allow it, enable auto-merge or merge the PR after checks pass.
15. Update the local-only PROGRESS file and move on to the next PR on the next `continue`.

## Local-Only PRD and PROGRESS Files

You should maintain local planning continuity with PRD and PROGRESS markdown files, but these are workflow artifacts and must stay local by default.

Rules:

- You may create and update local-only PRD and PROGRESS markdown files.
- You must never stage, commit, push, or include them in the PR unless I explicitly request that.
- You must never mention PRD, PROGRESS, phase plans, or planning artifacts in commit messages or PR descriptions.
- If such files are accidentally modified during the work, remove them from the PR scope before committing.

Recommended local naming:

- `PRD_<slug>.md`
- `PROGRESS_<slug>.md`

These files should capture:

- the PR goal
- exact scope
- acceptance criteria
- commit plan
- current branch
- PR URL
- checks run
- blockers
- next step

## Branch and PR Behavior

- Make the commits as if a human were doing logical sequential work.
- Avoid unrelated changes and avoid opportunistic refactors.
- Do not include phase or planning markdown files in PR commits.
- Follow [PR_GUIDELINES.md](PR_GUIDELINES.md) for branch names, commit messages, PR title, and PR body.
- Avoid mentioning phase files, PRDs, PROGRESS files, or plans in commits and PR descriptions.

## Skill Activation

Before starting any implementation, activate the relevant Laravel Boost skills:

- `laravel-best-practices` — for all PHP code
- `pest-testing` — for all test files
- `inertia-react-development` — for React/Inertia frontend code
- `tailwindcss-development` — for styling
- `wayfinder-development` — for frontend-to-backend route generation
- `fortify-development` — for auth-related changes

## AI SDK Usage

When implementing AI features (Phase 3+):

- Always use `search-docs` to check Laravel AI SDK documentation before writing agent code.
- Use the `laravel/ai` package — never call provider APIs directly.
- Mock all AI responses in tests — never make real API calls in the test suite.
- Use structured output schemas for deterministic response parsing.

## CI and Merge Policy

Assume the autonomous workflow should include:

- pushing the branch
- opening the PR
- checking CI status
- fixing CI failures when possible
- merging or enabling auto-merge when repository rules allow it

If GitHub branch protection still requires human review or a human approval that you cannot satisfy yourself, then:

- complete everything else autonomously
- report clearly that human approval is the only remaining blocker
- do not pretend the workflow is fully autonomous

## Verification Standard

Every PR must pass these checks before merge:

```bash
vendor/bin/pint --dirty --format agent       # PHP formatting
php artisan test --compact                    # Backend tests
npm run lint                                  # Frontend linting
npm run format:check                          # Frontend formatting
npm run types:check                           # TypeScript type checking
npm run build                                 # Frontend production build
```

## When To Stop

Do not stop after planning or after opening a PR if you can continue.

Only stop and ask me if:

- a product decision is genuinely ambiguous
- required credentials, secrets, or permissions are missing
- GitHub protections require a human approval you cannot provide
- CI fails for reasons you cannot resolve within the repository
- the next step would require destructive or high-risk work that is clearly outside the established pattern

## If No Active Next Step Exists

If the current phase files have no next steps, create the next phase branching strategy and implementation plan locally based on:

- [Portfolio_Analysis_Project_Specification.md](Portfolio_Analysis_Project_Specification.md)

These planning files are local workflow artifacts unless I explicitly ask for them to be committed.

## Additional Continuity Notes

- Before opening or updating a PR, make the feature branch literally descend from the latest remote `master`, not just an equivalent pre-merge branch tip. Rebase onto `origin/master` when needed and push with `--force-with-lease`.
- Keep local-only instruction and continuity markdown files uncommitted even when they are updated during the workflow.
- After implementing changes that include new migrations, run `php artisan migrate --no-interaction` before considering the branch QA-complete.
