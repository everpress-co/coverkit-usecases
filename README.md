# CoverKit Use Cases

Custom [CoverKit](https://coverkit.com) use cases you can install on your WordPress site. Each use case is a small plugin that registers a new image output (dimensions, field mappings, and optional front-end behavior) in the CoverKit template editor.

**Requires:** the main [CoverKit](https://coverkit.com) plugin, installed and active.

## Install a use case

### From a release zip (recommended)

1. Download the latest zip from the [Available use cases](#available-use-cases) table below (or browse [Releases](https://github.com/everpress-co/coverkit-usecases/releases) for versioned archives).
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

| Plugin | Use case slug | Purpose | Download |
| --- | --- | --- | --- |
| [coverkit-usecase-starter](plugins/coverkit-usecase-starter/) | `starter` | Minimal example — editor preview only, useful as a starting point | [Download zip](https://github.com/everpress-co/coverkit-usecases/releases/latest/download/coverkit-usecase-starter.zip) |
| [coverkit-usecase-dashboard-widget](plugins/coverkit-usecase-dashboard-widget/) | `dashboard_widget` | Site-wide wp-admin dashboard widget with generated image as background | [Download zip](https://github.com/everpress-co/coverkit-usecases/releases/latest/download/coverkit-usecase-dashboard-widget.zip) |

More use cases will appear here as they are added to the repository.

## Build your own use case

### Create with your IDE (no install)

Open your WordPress project in Cursor, Copilot, Claude Code, or any AI-enabled editor. Paste:

```text
Read this skill and create a CoverKit use case:

https://raw.githubusercontent.com/everpress-co/coverkit-usecases/main/SKILL.md
```

What happens next:

1. The agent reads [`SKILL.md`](SKILL.md) and asks a few questions about your image output and where it will be used.
2. You confirm the summary.
3. The agent creates `coverkit-usecase-<slug>/` in the directory where you invoked it (open your WordPress `plugins/` folder in the IDE first, or tell the agent another path).
4. Activate the plugin in **Plugins**, then enable the use case in the CoverKit template editor (**Use cases** sidebar).

Browse the skill: [github.com/everpress-co/coverkit-usecases/blob/main/SKILL.md](https://github.com/everpress-co/coverkit-usecases/blob/main/SKILL.md).

### Manual setup

Copy the [starter plugin](plugins/coverkit-usecase-starter/) or follow [`docs/create-a-use-case.md`](docs/create-a-use-case.md).

CoverKit’s official reference: [Custom use cases](https://docs.coverkit.com/user-guide/use-cases/custom-use-case/) and [use cases and output profiles](https://docs.coverkit.com/codebase/use-cases-and-output-profiles/).

## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md) and [`CHANGELOG.md`](CHANGELOG.md).
