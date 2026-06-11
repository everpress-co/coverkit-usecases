# Contributing

Thanks for contributing to CoverKit Use Cases!

## Development setup

1. Clone this repo into `wp-content/plugins/coverkit-usecases/`.
2. Install [CoverKit](https://coverkit.com) as a sibling plugin (`wp-content/plugins/coverkit/`). See [installation docs](https://docs.coverkit.com/user-guide/installation/).
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

## Version sync and releases

**Source of truth:** [`package.json`](package.json) `"version"`.

```bash
composer run sync:version        # propagate version to PHP headers and readme.txt files
composer run sync:version:check  # CI / pre-commit drift check
composer run package:release     # build install-ready zips to dist/
composer run package:release:verify
```

`sync:version` updates:

| Target | Fields |
| --- | --- |
| `coverkit-usecases.php` | `Version:` header, `COVERKIT_USECASES_VERSION` |
| Each `plugins/coverkit-usecase-*/{slug}.php` | `Version:` header, `COVERKIT_USECASE_*_VERSION` |
| Each `plugins/coverkit-usecase-*/readme.txt` | `Stable tag:` |

**Cut a release:** use **`/do-release`** in Cursor (see [`.cursor/commands/do-release.md`](.cursor/commands/do-release.md)). That creates a `release/x.y.z` branch, bumps `package.json`, runs sync + packaging verify, commits, and tags `vX.Y.Z`.

Pushing the tag triggers GitHub Actions, which runs `package:release` and attaches **one zip per folder** in `plugins/coverkit-usecase-*`. Each zip extracts to `wp-content/plugins/<slug>/` (WordPress-installable folder root).

## Changelog

User-facing changes belong in `## [Unreleased]` at the top of [`CHANGELOG.md`](CHANGELOG.md) during feature work. At release time, rename that section to the version number.

## Pull requests

- Target `develop` unless otherwise noted.
- Ensure README use cases table and skills table are updated.
- Do not commit `vendor/` changes unrelated to `composer.lock` updates from your branch.
