# CoverKit Use Cases

Custom [CoverKit](https://coverkit.com) use cases you can install on your WordPress site. Each use case is a small plugin that registers a new image output (dimensions, field mappings, and optional front-end behavior) in the CoverKit template editor.

**Requires:** the main [CoverKit](https://coverkit.com) plugin, installed and active.

## Install a use case

### From a release zip (recommended)

1. Open [Releases](https://github.com/everpress-co/coverkit-usecases/releases) and download the zip for the use case you want (e.g. `coverkit-usecase-starter-0.1.0.zip`).
2. In WordPress, go to **Plugins → Add New → Upload Plugin**, upload the zip, and activate it.
3. Edit a CoverKit template → **Use cases** sidebar → enable the use case and map your fields.

Each zip is a standalone WordPress plugin with `Requires Plugins: coverkit`.

### All use cases from this repo (development)

If you work from the full repository (e.g. to try everything or contribute):

1. Clone into `wp-content/plugins/coverkit-usecases/`.
2. Activate **CoverKit** and **CoverKit Use Cases** in **Plugins** — every use case under `plugins/` loads automatically.
3. Open a CoverKit template → **Use cases** sidebar to enable a use case.

## Use in the editor

1. Activate the use case plugin (and CoverKit).
2. Edit a CoverKit template.
3. In the **Use cases** sidebar, turn on the use case you installed.
4. Map template shapes to WordPress fields and preview the generated image.

## Available use cases

| Plugin | Use case slug | Purpose |
| --- | --- | --- |
| [coverkit-usecase-starter](plugins/coverkit-usecase-starter/) | `starter` | Minimal example — editor preview only, useful as a starting point |

More use cases will appear here as they are added to the repository.

## Build your own use case

### Create with your IDE (no install)

Open your WordPress project in Cursor, Copilot, Claude Code, or any AI-enabled editor. Paste:

```text
Read this skill and create a CoverKit use case:

https://raw.githubusercontent.com/everpress-co/coverkit-usecases/main/SKILL.md
```

What happens next:

1. The agent reads [`SKILL.md`](SKILL.md) and asks a few questions about your image output, dimensions, and fields.
2. You confirm the summary.
3. The agent creates `wp-content/plugins/coverkit-usecase-<slug>/`.
4. Activate the plugin in **Plugins**, then enable the use case in the CoverKit template editor (**Use cases** sidebar).

Browse the skill: [github.com/everpress-co/coverkit-usecases/blob/main/SKILL.md](https://github.com/everpress-co/coverkit-usecases/blob/main/SKILL.md).

### Manual or monorepo path

Use the [starter plugin](plugins/coverkit-usecase-starter/) as a template, or follow [`docs/create-a-use-case.md`](docs/create-a-use-case.md). Contributing to this repo? Use `/new-usecase <slug>` in Cursor or see [`CONTRIBUTING.md`](CONTRIBUTING.md).

CoverKit’s official reference: [Custom use cases](https://docs.coverkit.com/user-guide/use-cases/custom-use-case/) and [use cases and output profiles](https://docs.coverkit.com/codebase/use-cases-and-output-profiles/).

## Agent skills

Monorepo contributor skills (Cursor). Public use cases: use the [root `SKILL.md`](SKILL.md) prompt above — no install required.

<!-- skills-table:start -->
| Skill | Description |
| --- | --- |
| [`do-release`](.cursor/skills/do-release/SKILL.md) | Cut a use cases release — bump package.json, sync all use case plugin versions, verify install-ready zips, tag vX.Y.Z, and trigger GitHub Actions. Use when the user invokes /do-release or asks to ship a new use cases release. |
| [`lint-usecase`](.cursor/skills/lint-usecase/SKILL.md) | Run composer lint:php, fix PHPCS issues, verify README use-case table row exists, and regenerate the skills table for use case plugins in this repository. |
| [`new-usecase`](.cursor/skills/new-usecase/SKILL.md) | Scaffold plugins/coverkit-usecase-<slug>/ with a full WordPress plugin header, coverkit_init registration, and optional Use_Case subclass. Use when the user invokes /new-usecase or asks to add a custom CoverKit use case in this repo. |
| [`understand-use-cases`](.cursor/skills/understand-use-cases/SKILL.md) | CoverKit custom use case architecture: coverkit_register_use_case API, label-only vs subclass, built-in slugs to avoid, and docs.coverkit.com references. Use when onboarding or reviewing how use case plugins work. |
<!-- skills-table:end -->

## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md) and [`CHANGELOG.md`](CHANGELOG.md).
