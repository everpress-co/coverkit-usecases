---
name: do-usecase-release
description: Cut a use cases release — bump monorepo version first (default patch), sync changed plugins, verify install-ready zips, tag X.Y.Z, restore ## [Unreleased] on develop. Use when the user invokes /do-usecase-release or asks to ship a new use cases release.
---

# CoverKit Use Cases release

Follow [`.cursor/commands/do-usecase-release.md`](../../commands/do-usecase-release.md) exactly.

## Quick reference

| Step | Action |
| --- | --- |
| Branch | Start on `develop`; bump version first; create `release/<version>` |
| Version | Bump first from [`package.json`](../../../package.json) — default **patch**, or `minor` / `major` / exact `x.y.z` |
| Release sync | `composer run sync:version -- --changed-since <prev-tag> --update-wp-tested-up-to` (loader always; plugin semver only when changed; **all** plugins get WP compatibility) |
| Packages | `composer run package:release:verify` before tag |
| Tag | `<version>` (e.g. `0.1.1` — no `v` prefix) |
| Phase 3 | After merge to `develop`: fresh `## [Unreleased]` only — **no** post-ship version bump |
| CI | [`.github/workflows/release.yml`](../../../.github/workflows/release.yml) attaches one zip per `plugins/coverkit-usecase-*` |

## Not for

- Scaffolding use cases (`/new-usecase`)
- Day-to-day CHANGELOG edits on feature branches (use `## [Unreleased]` only)

## Related

- [`CONTRIBUTING.md`](../../../CONTRIBUTING.md) — version sync and release tags
