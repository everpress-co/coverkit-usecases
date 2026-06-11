# Contributing

Thanks for contributing to CoverKit Use Cases!

## Development setup

1. Clone this repo into `wp-content/plugins/coverkit-usecases/`.
2. Clone [CoverKit](https://github.com/everpress-co/coverkit) as a sibling plugin (`wp-content/plugins/coverkit/`).
3. Install dependencies:

   ```bash
   composer install
   ```

4. Activate **CoverKit** and **CoverKit Use Cases** in WordPress.

## Adding a use case

See [`docs/create-a-use-case.md`](docs/create-a-use-case.md) or use the **new-usecase** skill (`/new-usecase <slug>` in Cursor).

Every bootstrap under `plugins/coverkit-usecase-*/` must include a complete WordPress plugin header.

## Quality checks

```bash
composer run lint:php
COVERKIT_PLUGIN_DIR=../coverkit composer run test:php
composer run docs:skills
```

CI runs the same checks on pull requests. CoverKit is checked out from the `develop` branch for PHPUnit.

## Version sync

Keep these in sync when preparing a release:

| File | Field |
| --- | --- |
| `package.json` | `"version"` |
| `coverkit-usecases.php` | `Version:` header and `COVERKIT_USECASES_VERSION` |
| `CHANGELOG.md` | `## [x.y.z]` section for the release tag |

Release tags use the `v*` prefix (e.g. `v0.1.0`). The workflow creates one zip per folder in `plugins/coverkit-usecase-*`.

## Changelog

User-facing changes belong in `## [Unreleased]` at the top of [`CHANGELOG.md`](CHANGELOG.md) during feature work. At release time, rename that section to the version number.

## Pull requests

- Target `develop` unless otherwise noted.
- Ensure README use cases table and skills table are updated.
- Do not commit `vendor/` changes unrelated to `composer.lock` updates from your branch.
