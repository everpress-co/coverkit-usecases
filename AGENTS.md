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
- Avoid slug collisions with CoverKit built-ins (`opengraph`, `featured_image`, …) and bundled Sandbox (`sandbox`).

## Contributor skills (`.cursor/skills/`)

<!-- skills-table:start -->
| Skill | Description |
| --- | --- |
| [`do-usecase-release`](.cursor/skills/do-usecase-release/SKILL.md) | Cut a use cases release — bump package.json, sync loader and only changed use case plugin versions (git diff since previous tag), verify install-ready zips, tag X.Y.Z, and trigger GitHub Actions. Use when the user invokes /do-usecase-release or asks to ship a new use cases release. |
| [`lint-usecase`](.cursor/skills/lint-usecase/SKILL.md) | Run composer lint:php, fix PHPCS issues, verify README use-case table row exists, and regenerate the skills table for use case plugins in this repository. |
| [`new-usecase`](.cursor/skills/new-usecase/SKILL.md) | Scaffold plugins/coverkit-usecase-<slug>/ with a full WordPress plugin header, coverkit_init registration, and optional Use_Case subclass. Use when the user invokes /new-usecase or asks to add a custom CoverKit use case in this repo. |
| [`understand-use-cases`](.cursor/skills/understand-use-cases/SKILL.md) | CoverKit custom use case architecture: coverkit_register_use_case API, label-only vs subclass, built-in slugs to avoid, and docs.coverkit.com references. Use when onboarding or reviewing how use case plugins work. |
<!-- skills-table:end -->

Regenerate this table: `composer run docs:skills`

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
