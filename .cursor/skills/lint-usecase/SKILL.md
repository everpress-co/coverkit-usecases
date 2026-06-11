---
name: lint-usecase
description: >-
  Run composer lint:php, fix PHPCS issues, verify README use-case table row
  exists, and regenerate the skills table for coverkit-usecases plugins.
disable-model-invocation: true
---

# Lint use case plugin

Run before opening a PR or after editing use case PHP in this repo.

## Steps

1. From repo root, run:

   ```bash
   composer run lint:php
   ```

2. Auto-fix when safe:

   ```bash
   composer run lint:php:fix
   ```

3. Verify the **Use cases** table in [`README.md`](../../../README.md) lists every `plugins/coverkit-usecase-*` folder (slug + purpose).

4. Regenerate the Agent skills table:

   ```bash
   composer run docs:skills
   ```

5. Run tests when PHP changed:

   ```bash
   COVERKIT_PLUGIN_DIR=../coverkit composer run test:php
   ```

## PHPCS scope

[`/.phpcs.xml`](../../../.phpcs.xml) covers `coverkit-usecases.php` and `plugins/`. Match array alignment and WordPress coding standards (see starter).

## CI

GitHub Actions runs `lint:php`, PHPUnit (with CoverKit `develop` checkout), and `docs:skills` + README diff on every PR.

Do **not** commit unless the user asks.
