# Agent skills and entry points

This repo includes skills and hub files for AI-assisted development across editors and agents.

## Public — standalone use case (any WordPress site)

Paste in any IDE agent (no skill install):

```text
Read this skill and create a CoverKit use case:

https://raw.githubusercontent.com/everpress-co/coverkit-usecases/main/SKILL.md
```

The agent reads [root `SKILL.md`](../SKILL.md), asks discovery questions, confirms a summary, then scaffolds `wp-content/plugins/coverkit-usecase-<slug>/`.

## Monorepo — Cursor

| Invocation | Skill |
| --- | --- |
| `/new-usecase <slug>` | [`.cursor/skills/new-usecase/SKILL.md`](../.cursor/skills/new-usecase/SKILL.md) |
| `/do-release` | [`.cursor/skills/do-release/SKILL.md`](../.cursor/skills/do-release/SKILL.md) |
| Read skill directly | `.cursor/skills/understand-use-cases/SKILL.md`, `.cursor/skills/lint-usecase/SKILL.md` |

Thin rule [`.cursor/rules/new-usecase.mdc`](../.cursor/rules/new-usecase.mdc) defers to the **new-usecase** skill for files under `plugins/`.

## Other agents

| Agent | Entry file |
| --- | --- |
| **Universal** | [`AGENTS.md`](../AGENTS.md) |
| **GitHub Copilot** | [`.github/copilot-instructions.md`](../.github/copilot-instructions.md) |
| **Claude Code** | [`CLAUDE.md`](../CLAUDE.md) |

Tell the agent to read the relevant `SKILL.md` under `.cursor/skills/` — content is editor-agnostic.

## Contributor skills table

The **Contributor skills** section in [`AGENTS.md`](../AGENTS.md) is generated from skill frontmatter:

```bash
composer run docs:skills
```

CI fails if skills are added without updating AGENTS.md.

## Typical workflows

1. **Standalone use case** — root `SKILL.md` URL prompt → discovery → scaffold in `wp-content/plugins/`
2. **Monorepo use case** — `new-usecase` skill → lint → add README table row → `docs:skills`
3. **Onboarding** — `understand-use-cases` skill + `docs/architecture.md`
4. **Before PR** — `lint-usecase` skill (`lint:php`, tests, README checks)
5. **Release** — `/do-release` on `develop` → tag `vX.Y.Z` → GitHub Actions publishes per-use-case zips
