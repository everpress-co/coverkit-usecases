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

Use the [starter plugin](plugins/coverkit-usecase-starter/) as a template, or follow the step-by-step guide in [`docs/create-a-use-case.md`](docs/create-a-use-case.md).

CoverKit’s official reference: [Custom use cases](https://github.com/everpress-co/coverkit/blob/develop/docs/src/content/docs/user-guide/use-cases/custom-use-case.md).

## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md) and [`CHANGELOG.md`](CHANGELOG.md).
