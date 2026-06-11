---
description: Prepare and cut a coverkit-usecases monorepo release — version sync, packaging verify, release branch and tag
---

# Prepare and cut a CoverKit Use Cases release

Run a **two-phase** release workflow. **Stop on any failure** — do not commit, tag, or push through red tests or an empty `## [Unreleased]` without calling it out and getting explicit user direction.

**Changelog-first:** Day-to-day work updates [`CHANGELOG.md`](CHANGELOG.md) under `## [Unreleased]`. This command renames that section to the release version and opens a fresh `## [Unreleased]`.

## Overview

```text
develop
  → pre-flight + resolve version
  → create release/<version> branch
  → bump package.json, sync loader + changed plugins only, update CHANGELOG
  → package:release:verify + tests
  → checkpoint: summary + ask
  → (if yes) commit + tag v<version> on release/<version>
  → checkpoint: optional push
```

Pushing the tag triggers [`.github/workflows/release.yml`](.github/workflows/release.yml) (unit tests, `package:release`, one install-ready zip per use case on the GitHub Release). Tag format: **`v<version>`** (e.g. `v0.1.1`).

---

## Phase 1 — Pre-flight on `develop` (hard stops)

Run **before** creating the release branch. Do **not** auto-merge feature branches into `develop`.

| Check | Action if fail |
| --- | --- |
| **Current branch** | Must be **`develop`**. If on `feature/*`, `release/*`, or anything else → **stop**. Instruct: merge/rebase into `develop` first. |
| **Sync `develop`** | `git fetch origin`. Compare `develop` to `origin/develop`; `git pull origin develop` if behind (warn if offline / fetch fails). |
| **Working tree** | Prefer a **clean** tree on `develop`. If uncommitted changes exist → **stop** and show `git status -sb`. Continue only if the user **explicitly** accepts releasing with those changes. |

Then **resolve release version** (next section). After resolution:

| Check | Action if fail |
| --- | --- |
| **Duplicate branch/tag** | After fetch: `release/<version>` must not exist locally or on `origin`. `git tag -l 'v<version>'` must be empty locally; check remote tags after fetch. → **stop**. |
| **CHANGELOG ready** | `## [Unreleased]` must have **at least one** bullet (`- ` lines). Empty section → **stop** before branching. |
| **Optional bump override** | If the user said `patch`, `minor`, or `major`, use that bump type (see version rules). |

---

## Resolve release version

1. Read `current` from [`package.json`](package.json): `jq -r '.version'`.
2. **Default:** `release` = **patch bump** of `current` (e.g. `0.1.0` → `0.1.1`).
3. **Override:** `minor` / `major` / explicit `0.2.0` when the user requests it.
4. Echo: `Releasing <release> (current package: <current>).`

---

## Phase 2 — Create `release/<version>` branch

Still on **`develop`**, after pre-flight and version resolution succeed:

```bash
git checkout develop
git pull origin develop   # if network OK; else warn and continue with local develop
git checkout -b release/<version>
```

All file edits, tests, commits, and tags happen **on this branch**, not on `develop`.

---

## Update versioned files

**Monorepo version** (always bump):

```bash
npm version <release> --no-git-tag-version
```

| Target | Source |
| --- | --- |
| [`package.json`](package.json) + [`package-lock.json`](package-lock.json) | `npm version` |
| [`coverkit-usecases.php`](coverkit-usecases.php) | `sync:version` — `Version:` + `COVERKIT_USECASES_VERSION` (always) |

**Per-use-case plugin versions** — bump **only when that plugin changed** since the previous release. Unchanged plugins keep their existing `Version:` / `Stable tag:`.

1. Resolve previous tag after `git fetch --tags`:

   ```bash
   PREV_TAG=$(git describe --tags --abbrev=0 2>/dev/null || true)
   ```

   - If `PREV_TAG` is empty (first release), sync all plugins.
   - Otherwise use `PREV_TAG` as the diff base (e.g. `v0.1.1`).

2. List changed plugins (for the release summary):

   ```bash
   for dir in plugins/coverkit-usecase-*/; do
     slug=$(basename "$dir")
     git diff --quiet "${PREV_TAG}"..HEAD -- "plugins/${slug}/" || echo "changed: ${slug}"
   done
   ```

3. Sync loader + changed plugins only:

   ```bash
   if [[ -z "$PREV_TAG" ]]; then
     composer run sync:version -- --all-plugins
   else
     composer run sync:version -- --changed-since "$PREV_TAG"
   fi
   ```

| Target | When synced |
| --- | --- |
| Each `plugins/coverkit-usecase-*/{slug}.php` | Only if `git diff <prev-tag>..HEAD -- plugins/<slug>/` is non-empty |
| Each `plugins/coverkit-usecase-*/readme.txt` | Same rule as bootstrap (`Stable tag:`) |

`--changed-since` uses `git diff` on the plugin folder only (not monorepo root files). New plugins under `plugins/` count as changed. Do **not** run plain `composer run sync:version` during release — that bumps every plugin.

Release zips use each plugin’s own `Version:` header for the filename (`<slug>-<plugin-version>.zip`), so unchanged plugins keep their prior zip name on the GitHub Release.

---

## CHANGELOG

Use **today’s date** in local timezone (`YYYY-MM-DD`).

1. Rename `## [Unreleased]` → `## [<release>] — YYYY-MM-DD` (keep bullets).
2. Insert a fresh empty `## [Unreleased]` at the top of [`CHANGELOG.md`](CHANGELOG.md).

---

## Verification (stop on failure)

Run from repo root in order:

| Step | Command |
| --- | --- |
| Packaging | `composer run package:release:verify` |
| PHP lint | `composer run lint:php` |
| PHP tests | `COVERKIT_PLUGIN_DIR=../coverkit composer run test:php` |

`package:release:verify` builds `dist/<slug>-<plugin-version>.zip` locally (version from each bootstrap `Version:` header) and asserts each zip contains `<slug>/<slug>.php`. Do **not** commit `dist/` or `build/release/` (gitignored; CI rebuilds on tag push).

**Scope warning:** Before commit, run `git status`. If files outside version/CHANGELOG paths changed, **warn** and only stage intended release files unless the user says otherwise.

---

## Checkpoint 1 — summary (required before commit/tag)

Present a compact summary:

- Branch: `release/<version>`
- Release version, loader sync, and which plugins were version-bumped vs left unchanged (from `git diff`)
- CHANGELOG section renamed + new `## [Unreleased]`
- Expected GitHub Release assets: `dist/coverkit-usecase-*-<plugin-version>.zip` (one per folder in `plugins/`; version per plugin header)
- Test / lint / packaging results (pass/fail)
- Files to be committed (list paths)
- Planned tag: `v<version>`

**Ask explicitly:** “Proceed with commit and tag on `release/<version>`?”

If **no** → **stop**. Leave the release branch and local changes for the user to finish manually.

---

## Commit and tag (only after “yes”)

On **`release/<version>`**, stage release-intended files:

```bash
git add package.json package-lock.json CHANGELOG.md coverkit-usecases.php plugins/
git commit -m "release: <version>"
git tag v<version>
```

- **Stop** if a commit hook fails.
- Do **not** push unless the user confirms in Checkpoint 2.

---

## Checkpoint 2 — push (optional)

Ask: “Push `release/<version>` and tag `v<version>` to origin?”

If **yes**:

```bash
git push -u origin release/<version>
git push origin v<version>
```

Note: merge `release/<version>` into `develop` or `main` per team policy (do not auto-merge).

---

## Post-release checklist (human)

After push, verify:

- [ ] GitHub Actions **Create Release** completed (unit tests → `package:release` → release assets)
- [ ] GitHub Release has one zip per `plugins/coverkit-usecase-*`
- [ ] Each zip extracts to `wp-content/plugins/<slug>/`
- [ ] Merge `release/<version>` per team policy

**Out of scope:** monorepo loader zip, README changelog sync, Freemius.

---

## Example invocation

User: **`/do-usecase-release`**

Agent: pre-flight on `develop` → resolve version → `release/<version>` → bump monorepo + sync loader/changed plugins (`--changed-since`) + CHANGELOG → verify → summary → user “proceed” → commit + tag `v<version>` → optional push ask.

User: **`/do-usecase-release minor`** — force minor bump.
