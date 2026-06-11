# CoverKit Use Cases

[![Unit tests](https://github.com/everpress-co/coverkit-usecases/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/everpress-co/coverkit-usecases/actions/workflows/unit-tests.yml)

Monorepo for **custom CoverKit use cases**. Each use case lives in its own plugin under `plugins/`, loaded automatically by the root WordPress plugin.

Requires the main [CoverKit](https://coverkit.com) plugin to be installed and active.

## Quick start

### Monorepo (all use cases)

1. Clone into `wp-content/plugins/coverkit-usecases/`.
2. Run `composer install`.
3. Activate **CoverKit** and **CoverKit Use Cases** in **Plugins** — all use cases under `plugins/` load automatically.
4. Open a CoverKit template → **Use cases** sidebar to enable a custom use case.

### Single use case (release zip)

1. Download a zip from [Releases](https://github.com/everpress-co/coverkit-usecases/releases) (e.g. `coverkit-usecase-starter-0.1.0.zip`).
2. Extract to `wp-content/plugins/coverkit-usecase-<slug>/`.
3. Activate the use case plugin (requires **CoverKit**).

Each release zip is a valid standalone WordPress plugin with `Requires Plugins: coverkit`.

## Create your first use case

See [`docs/create-a-use-case.md`](docs/create-a-use-case.md) for the step-by-step guide.

Or use the Cursor command **`/new-usecase <slug>`** — it scaffolds from the starter template (see [Agent skills](#agent-skills) below).

CoverKit reference: [Custom use cases](https://github.com/everpress-co/coverkit/blob/develop/docs/src/content/docs/user-guide/use-cases/custom-use-case.md).

## Structure

```
coverkit-usecases/
├── coverkit-usecases.php           # WordPress plugin — loads all use cases
├── plugins/
│   └── coverkit-usecase-starter/   # example / test use case
├── docs/                           # developer documentation
├── tests/php/                      # PHPUnit
├── composer.json                   # PHP dev tools (PHPCS, PHPUnit)
└── package.json                    # version + npm script wrappers
```

## Agent skills

<!-- skills-table:start -->
| Skill | Description |
| --- | --- |
| [`do-release`](.cursor/skills/do-release/SKILL.md) | Cut a coverkit-usecases monorepo release — bump package.json, sync all use case plugin versions, verify install-ready zips, tag vX.Y.Z, and trigger GitHub Actions. Use when the user invokes /do-release or asks to ship a new use cases release. |
| [`lint-usecase`](.cursor/skills/lint-usecase/SKILL.md) | Run composer lint:php, fix PHPCS issues, verify README use-case table row exists, and regenerate the skills table for coverkit-usecases plugins. |
| [`new-usecase`](.cursor/skills/new-usecase/SKILL.md) | Scaffold plugins/coverkit-usecase-<slug>/ with a full WordPress plugin header, coverkit_init registration, and optional Use_Case subclass. Use when the user invokes /new-usecase or asks to add a custom CoverKit use case in this repo. |
| [`understand-use-cases`](.cursor/skills/understand-use-cases/SKILL.md) | Onboarding for the coverkit-usecases monorepo: loader vs standalone install, coverkit_register_use_case API, label-only vs subclass, built-in slugs to avoid, and links to CoverKit reference classes. |
<!-- skills-table:end -->

See also [`docs/agents.md`](docs/agents.md), [`AGENTS.md`](AGENTS.md).

## Scripts

| Command | Description |
| --- | --- |
| `composer run lint:php` | Run PHPCS on plugin PHP files |
| `composer run lint:php:fix` | Auto-fix PHPCS issues |
| `COVERKIT_PLUGIN_DIR=../coverkit composer run test:php` | PHPUnit (requires sibling CoverKit checkout) |
| `composer run docs:skills` | Regenerate Agent skills table in this README |
| `composer run sync:version` | Propagate `package.json` version to all plugin headers |
| `composer run sync:version:check` | Fail if version fields drift from `package.json` |
| `composer run package:release` | Build install-ready zips to `dist/` (one per use case) |
| `composer run package:release:verify` | Build zips and verify WordPress folder structure |
| `npm run lint:php` | Same as `composer run lint:php` |

CI checks out [CoverKit `develop`](https://github.com/everpress-co/coverkit/tree/develop) for PHPUnit.

## Releases

- **Monorepo:** clone this repository; activate **CoverKit Use Cases**.
- **Per use case:** download zips from [Releases](https://github.com/everpress-co/coverkit-usecases/releases) — one zip per folder in `plugins/coverkit-usecase-*` (e.g. `coverkit-usecase-starter-0.1.0.zip` → `wp-content/plugins/coverkit-usecase-starter/`).

Version lives in `package.json`; `composer run sync:version` keeps every use case plugin in sync. Cut releases with **`/do-release`** (tags `v*` trigger [Create Release](.github/workflows/release.yml)).

## Use cases

| Folder | Use case slug | Purpose |
| --- | --- | --- |
| [coverkit-usecase-starter](plugins/coverkit-usecase-starter/) | `starter` | Minimal editor-only test use case |

## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md) and [`CHANGELOG.md`](CHANGELOG.md).
