# Project Builder Skill — Setup & Usage Guide

## What This Skill Does

The Project Builder skill teaches Claude Code how to produce a complete set of planning artifacts for incremental, PR-by-PR, AI-assisted development. It codifies the planning patterns used across the `portifolio-analysis` and `docint` projects into a reusable skill that works with any Laravel + React project.

When activated, Claude follows a structured investigation protocol, asks clarifying questions, and generates 6 interconnected markdown files that serve as the single source of truth for the entire project lifecycle.

---

## Prerequisites

- **Claude Code** installed (CLI, VS Code extension, or desktop app)
- A **git repository** initialized for your project
- (Optional) **oh-my-claudecode** installed for the Ralph Loop autonomous workflow

---

## Installation

The skill lives at `.claude/skills/project-builder/SKILL.md` inside your repository. There are two ways to set it up:

### Option A: Copy from an existing project

```bash
# From your new project root
mkdir -p .claude/skills/project-builder
cp /path/to/portifolio-analysis/.claude/skills/project-builder/SKILL.md \
   .claude/skills/project-builder/SKILL.md
```

### Option B: Create the directory and paste the file

```bash
mkdir -p .claude/skills/project-builder
# Then copy the SKILL.md content into .claude/skills/project-builder/SKILL.md
```

### Verify installation

Open Claude Code in your project and check that `project-builder` appears in the skill list. You can confirm by typing `/project-builder` — if it autocompletes, the skill is active.

---

## How To Use It

### 1. Planning a Brand New Project

Open Claude Code in your project directory and describe what you want to build. The skill activates automatically on trigger phrases.

**Example prompts:**

```
Plan this project: I want to build a document processing app that extracts
data from PDFs using AI, classifies the extracted items, and lets users
review the results. The app should use Laravel 13 + React 19 with
real-time updates.
```

```
Create a PRD for rebuilding the Python app at ../my-python-app as a
Laravel web application. Read all the source files first.
```

```
Scaffold the project planning artifacts for this repo.
```

**What happens:**

1. Claude reads your codebase (`composer.json`, `package.json`, `CLAUDE.md`) to understand the stack
2. If porting an existing app, Claude reads ALL source files from the original
3. Claude asks 2-4 rounds of clarifying questions (product type, auth model, integrations, etc.)
4. Claude generates all 6 planning artifacts in dependency order

### 2. Generating Specific Artifacts

You don't have to generate everything at once. You can request individual pieces:

```
Create a CONTINUE_PROJECT_CODEX for this project
```

```
Write the Phase 2 branching strategy and implementation plan
```

```
Create PR guidelines for this repository
```

### 3. Using the Ralph Loop (Autonomous Continue)

After the planning artifacts exist, the Ralph Loop takes over. Say `continue` and Claude will:

1. Read all planning files in the correct order
2. Inspect the repo state (branches, merged PRs, open PRs)
3. Infer the next logical PR from the phase plan
4. Create a local PRD and PROGRESS file for that PR
5. Branch, implement, test, commit, push, and open a PR
6. Monitor CI and fix failures
7. Report status and wait for the next `continue`

**First continue:**
```
continue
```

**Subsequent continues (after merging the previous PR):**
```
continue
```

Each `continue` ships one PR. The agent stops only when blocked (ambiguous decisions, missing credentials, CI failures it can't fix, or branch protection requiring human approval).

### 4. Manual Invocation

If automatic activation doesn't trigger, invoke explicitly:

```
/project-builder
```

Then follow up with your request in the next message.

---

## Artifacts Reference

After a full scaffold, your project root will contain:

```
your-project/
├── {Project}_Project_Specification.md   # The "Total PRD" — master spec
├── CONTINUE_PROJECT_CODEX.md            # Ralph Loop — autonomous continue
├── PR_GUIDELINES.md                     # Branch, commit, PR conventions
├── AGENT_NOTES.md                       # CI polling, deletion safety, responsibility split
├── PHASE1_BRANCHING_STRATEGY.md         # First phase PR order and dependencies
├── PHASE1_IMPLEMENTATION_PLAN.md        # First phase step-by-step
│
│   # Created during Ralph Loop execution:
├── PRD_feature-name.md                  # Per-PR scope document (local-only)
├── PROGRESS_feature-name.md             # Per-PR completion tracker (local-only)
│
│   # Created when project scope ends:
└── PHASE{N+1}_BRANCHING_STRATEGY.local.md  # Stop signal — no more phases
```

### What each file does

| File | Read by | Purpose |
|---|---|---|
| Project Specification | Agent + Human | Single source of truth for all features, models, and phases |
| CONTINUE_PROJECT_CODEX | Agent | Instructions for the autonomous continue loop |
| PR_GUIDELINES | Agent | How to name branches, write commits, format PR descriptions |
| AGENT_NOTES | Agent | Operational rules: CI polling, deletion safety, git patterns |
| Phase Branching Strategy | Agent | Which PRs to build and in what order for the current phase |
| Phase Implementation Plan | Agent | Exact commands and steps for each PR in the phase |
| PRD_{slug} | Agent | Scope, acceptance criteria, and commit plan for one PR |
| PROGRESS_{slug} | Agent | Status tracker for one PR across context windows |

### Local-only rule

All these files are **local-only workflow artifacts**. They must never be committed to PRs unless you explicitly ask for it. Add them to your `.gitignore` or simply leave them untracked.

The reason: PRs should describe delivered capability ("added user role authorization"), not planning process ("completed Phase 1 PR 1 from the implementation plan").

---

## Customizing the Skill

### Changing the tech stack

The skill is stack-aware but not stack-locked. It references Laravel + React patterns because that's the origin project, but the structural patterns (phases, PRDs, PROGRESS files, Ralph Loop) work for any stack. To adapt:

1. Edit the **Verification Standard** section in the CODEX template to match your project's check commands
2. Edit the **Skill Activation** section to reference your project's relevant skills
3. Edit the **Implementation Plan** examples to use your stack's CLI commands

### Adjusting phase granularity

The default rules are:
- Small project (< 10 models): 3-5 phases, 2-3 PRs each
- Medium project (10-20 models): 5-7 phases, 2-4 PRs each
- Large project (20+ models): 7-10 phases, 3-5 PRs each

Override these by telling Claude your preference during the clarifying questions phase:

```
I want very small PRs — no more than 2 per phase, each under 300 lines changed.
```

### Adding project-specific rules

Add rules to the **Quality Rules** section at the bottom of `SKILL.md`. For example, if your project requires Portuguese domain terms:

```markdown
- **Domain terms in Portuguese, code in English.** Keep domain field names
  in Portuguese (ativo, posicao, classe) but use English for class names,
  methods, and variables.
```

---

## Workflow Examples

### Example 1: Port a Python app to Laravel

```
User: Plan this project. I want to rebuild the Python app at ../my-python-app
      as a Laravel 13 + React 19 web application. Read all the source files.

Claude: [Reads all Python files, asks 2-4 clarifying questions]

User: [Answers questions about UI scope, auth model, integrations]

Claude: [Generates all 6 planning artifacts]

User: continue

Claude: [Ships PR 1.1: User Roles and Authorization]

User: [Reviews and merges PR]

User: continue

Claude: [Ships PR 1.2: Core Domain Models and Factories]

... (repeat until project is complete)
```

### Example 2: Plan a greenfield project from a vague idea

```
User: Plan this project: I want a SaaS app where companies can upload
      invoices and we extract line items using AI.

Claude: Before I draft the PRD, I need to understand a few decisions:
        1. UI scope: full web UI or headless API?
        2. Auth: multi-tenant? Role-based within each tenant?
        3. AI provider: OpenAI, Anthropic, or provider-agnostic?
        4. Real-time: should extraction status update live?

User: Full web UI, multi-tenant with admin/member roles, provider-agnostic
      using Laravel AI SDK, yes real-time with Reverb.

Claude: [Generates all 6 planning artifacts]
```

### Example 3: Add a new phase to an existing project

```
User: Create the Phase 3 branching strategy and implementation plan based
      on the Project Specification.

Claude: [Reads the spec, checks what Phases 1-2 delivered, generates
         PHASE3_BRANCHING_STRATEGY.md and PHASE3_IMPLEMENTATION_PLAN.md]
```

---

## Troubleshooting

### Skill doesn't activate automatically

**Cause:** The skill description's trigger phrases didn't match your message.

**Fix:** Invoke manually with `/project-builder`, or use one of the exact trigger phrases: "plan this project", "create a PRD", "scaffold the project".

### Claude generates artifacts with wrong format

**Cause:** The skill may not have been loaded into context.

**Fix:** Start a new conversation and invoke `/project-builder` explicitly before making your request.

### Ralph Loop stops unexpectedly

**Cause:** One of the legitimate stop conditions was hit.

**Fix:** Check Claude's last message. It will tell you exactly why it stopped:
- Ambiguous product decision → answer the question and say `continue`
- Missing credentials → add the credentials and say `continue`
- Human approval required → review and merge the PR, then say `continue`
- CI failure → check the failure, fix if needed, then say `continue`

### PRD is missing features from the source app

**Cause:** Claude may not have read all source files during investigation.

**Fix:** Explicitly tell Claude which files to read:
```
Read ALL files in ../my-python-app before drafting the PRD.
Have you read the prompts.py and classification.py files?
```

---

## Key Principles

These principles come from Anthropic's official prompting best practices and are baked into the skill:

1. **Be clear and direct.** The skill uses specific instructions with exact commands, not vague guidance. "Run `php artisan make:model Submission -mf --no-interaction`" beats "create a model."

2. **Add context for every rule.** Each section explains *why* the rule exists, not just what it is. Claude generalizes better from explanations than from bare commands.

3. **Use examples to steer output.** The skill includes good/bad example pairs for every artifact type. 3-5 diverse examples are more reliable than lengthy instructions.

4. **Structure with XML tags.** The skill uses `<role>`, `<context>`, `<instructions>`, and `<examples>` tags so Claude can distinguish instructions from content from examples.

5. **Tell what to DO, not what to avoid.** "Each phase has a one-sentence goal, checkbox deliverables, and a concrete validation scenario" produces better output than "don't make vague phases."

6. **Assign a role.** The skill opens with a role definition ("senior Product Manager and Software Architect") that focuses Claude's behavior for the entire session.
