# Agent skills and entry points

This repo includes skills and hub files for AI-assisted development across editors and agents.

## Cursor

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

## README skills table

The **Agent skills** section in [`README.md`](../README.md) is generated from skill frontmatter:

```bash
composer run docs:skills
```

CI fails if skills are added without updating README.

## Typical workflows

1. **New use case** — `new-usecase` skill → lint → add README table row → `docs:skills`
2. **Onboarding** — `understand-use-cases` skill + `docs/architecture.md`
3. **Before PR** — `lint-usecase` skill (`lint:php`, tests, README checks)
4. **Release** — `/do-release` on `develop` → tag `vX.Y.Z` → GitHub Actions publishes per-use-case zips
