---
name: do-usecase-release
description: Cut a use cases release — bump package.json, sync loader and only changed use case plugin versions (git diff since previous tag), verify install-ready zips, tag X.Y.Z, and trigger GitHub Actions. Use when the user invokes /do-usecase-release or asks to ship a new use cases release.
---

# CoverKit Use Cases release

Follow [`.cursor/commands/do-usecase-release.md`](../../commands/do-usecase-release.md) exactly.

## Quick reference

| Step | Action |
| --- | --- |
| Branch | Start on `develop`; create `release/<version>` |
| Version source | [`package.json`](../../../package.json) |
| Sync | `composer run sync:version -- --changed-since <prev-tag>` after `npm version` (loader always; plugins only when `git diff` shows changes) |
| Packages | `composer run package:release:verify` before tag |
| Tag | `<version>` (e.g. `0.1.1` — no `v` prefix) |
| CI | [`.github/workflows/release.yml`](../../../.github/workflows/release.yml) attaches one zip per `plugins/coverkit-usecase-*` |

## Not for

- Scaffolding use cases (`/new-usecase`)
- Day-to-day CHANGELOG edits on feature branches (use `## [Unreleased]` only)

## Related

- [`CONTRIBUTING.md`](../../../CONTRIBUTING.md) — version sync and release tags
