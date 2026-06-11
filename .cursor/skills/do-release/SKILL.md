---
name: do-release
description: Cut a use cases release — bump package.json, sync all use case plugin versions, verify install-ready zips, tag vX.Y.Z, and trigger GitHub Actions. Use when the user invokes /do-release or asks to ship a new use cases release.
---

# CoverKit Use Cases release

Follow [`.cursor/commands/do-release.md`](../../commands/do-release.md) exactly.

## Quick reference

| Step | Action |
| --- | --- |
| Branch | Start on `develop`; create `release/<version>` |
| Version source | [`package.json`](../../../package.json) |
| Sync | `composer run sync:version` after `npm version` |
| Packages | `composer run package:release:verify` before tag |
| Tag | `v<version>` (e.g. `v0.1.1`) |
| CI | [`.github/workflows/release.yml`](../../../.github/workflows/release.yml) attaches one zip per `plugins/coverkit-usecase-*` |

## Not for

- Scaffolding use cases (`/new-usecase`)
- Day-to-day CHANGELOG edits on feature branches (use `## [Unreleased]` only)

## Related

- [`CONTRIBUTING.md`](../../../CONTRIBUTING.md) — version sync and release tags
- Main CoverKit `/do-release` — different repo; do not mix tag formats (CoverKit has no `v` prefix; usecases uses `v`).
