# CoverKit Use Cases — agent hub

Public monorepo for **custom CoverKit use cases**. Each folder under `plugins/coverkit-usecase-*` is a standalone WordPress plugin that registers one use case on `coverkit_init`.

Requires the main [CoverKit](https://coverkit.com) plugin.

## Public — create a use case in any IDE

Paste in your agent (no install):

```text
Read this skill and create a CoverKit use case:

https://raw.githubusercontent.com/everpress-co/coverkit-usecases/main/SKILL.md
```

See [root `SKILL.md`](SKILL.md) and [`docs/create-a-use-case.md`](docs/create-a-use-case.md).

## Monorepo rules

- One use case per plugin folder; do not combine multiple registrations in one bootstrap.
- Every bootstrap needs a **full WordPress plugin header** (installable from release zips).
- Register on `coverkit_init` priority **5**; defer subclass `require_once` until that callback.
- Avoid slug collisions with CoverKit built-ins (`opengraph`, `featured_image`, `sandbox`, …).

## Skills (`.cursor/skills/`)

| Skill | Use when |
| --- | --- |
| **new-usecase** | Scaffold `plugins/coverkit-usecase-<slug>/` |
| **understand-use-cases** | Onboarding, architecture questions |
| **lint-usecase** | PHPCS, README table, tests before PR |
| **do-release** | Cut a monorepo release (`/do-release`) |

Regenerate the README skills table: `composer run docs:skills`

## Docs

- [`docs/create-a-use-case.md`](docs/create-a-use-case.md) — step-by-step
- [`docs/architecture.md`](docs/architecture.md) — loader, headers, registration
- [`docs/agents.md`](docs/agents.md) — skills across Cursor, Copilot, Claude Code

## Commands

```bash
composer run lint:php
COVERKIT_PLUGIN_DIR=../coverkit composer run test:php
composer run docs:skills
composer run sync:version:check
composer run package:release:verify
```

## Reference

- Starter template: `plugins/coverkit-usecase-starter/`
- CoverKit custom use cases: https://docs.coverkit.com/user-guide/use-cases/custom-use-case/
