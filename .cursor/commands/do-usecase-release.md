---
description: Prepare and cut a coverkit-usecases monorepo release — version sync, packaging verify, release branch and tag, then post-release version bump on develop
---

# Prepare and cut a CoverKit Use Cases release

Run a **three-phase** release workflow. **Stop on any failure** — do not commit, tag, or push through red tests or an empty `## [Unreleased]` without calling it out and getting explicit user direction.

**Changelog-first:** Day-to-day work updates [`CHANGELOG.md`](CHANGELOG.md) under `## [Unreleased]`. This command renames that section to the release version, tags the version already on `develop`, and **Phase 3** bumps `develop` to the next version for ongoing feature work.

## Overview

```text
develop (version = release, ## [Unreleased] has bullets)
  → pre-flight: release = package.json version (no patch bump)
  → release/<version> — sync changed plugins, CHANGELOG rename (no monorepo bump)
  → package:release:verify + tests
  → checkpoint → commit + tag
  → checkpoint → optional push
  → merge release/<version> → develop (user confirms)
  → Phase 3: bump monorepo to next version + loader sync + fresh ## [Unreleased]
```

Pushing the tag triggers [`.github/workflows/release.yml`](.github/workflows/release.yml) (unit tests, `package:release`, one install-ready zip per use case on the GitHub Release). Tag format: **`<version>`** — semver **without** a `v` prefix (e.g. `0.1.1`, not `v0.1.1`).

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
| **Duplicate branch/tag** | After fetch: `release/<version>` must not exist locally or on `origin`. `git tag -l '<version>'` must be empty locally; check remote tags after fetch. → **stop**. |
| **Version file drift** | [`package.json`](package.json) `"version"` must match [`coverkit-usecases.php`](coverkit-usecases.php) `Version:` and `COVERKIT_USECASES_VERSION`. → **stop** on drift. |
| **CHANGELOG ready** | `## [Unreleased]` must have **at least one** bullet (`-` lines). Empty section → **stop** before branching. |

---

## Resolve release version

1. Read `release` from [`package.json`](package.json): `jq -r '.version'`.
2. Verify it matches [`coverkit-usecases.php`](coverkit-usecases.php) `Version:` — **stop** on drift.
3. Echo: `Releasing <release> (version already on develop).`
4. **Do not** patch-bump or minor-bump when resolving `release`. The version on `develop` **is** the release.

Optional `patch` / `minor` / `major` from the user applies only in [Phase 3](#phase-3--post-release-bump-on-develop) when bumping to the **next** development version.

---

## Phase 2 — Create `release/<version>` branch

Still on **`develop`**, after pre-flight and version resolution succeed:

```bash
git checkout develop
git pull origin develop   # if network OK; else warn and continue with local develop
git checkout -b release/<version>
```

All release edits, tests, commits, and tags happen **on this branch**, not on `develop`.

---

## Sync changed plugin versions (no monorepo bump)

**Do not** run `npm version` on the release branch — [`package.json`](package.json) already equals `<release>`.

**Per-use-case plugin versions** — sync **only when that plugin changed** since the previous release so changed plugins match the monorepo version. Unchanged plugins keep their existing `Version:` / `Stable tag:`.

1. Resolve previous tag after `git fetch --tags`:

   ```bash
   PREV_TAG=$(git describe --tags --abbrev=0 2>/dev/null || true)
   ```

   - If `PREV_TAG` is empty (first release), sync all plugins.
   - Otherwise use `PREV_TAG` as the diff base (e.g. `0.1.1`).

2. List changed plugins (for the release summary):

   ```bash
   for dir in plugins/coverkit-usecase-*/; do
     slug=$(basename "$dir")
     git diff --quiet "${PREV_TAG}"..HEAD -- "plugins/${slug}/" || echo "changed: ${slug}"
   done
   ```

3. Sync loader + changed plugins only (and WordPress compatibility for **all** plugins):

   ```bash
   if [[ -z "$PREV_TAG" ]]; then
     composer run sync:version -- --all-plugins --update-wp-tested-up-to
   else
     composer run sync:version -- --changed-since "$PREV_TAG" --update-wp-tested-up-to
   fi
   ```

| Target | When synced |
| --- | --- |
| [`coverkit-usecases.php`](coverkit-usecases.php) | Always (`Version:` + `COVERKIT_USECASES_VERSION`) |
| Each `plugins/coverkit-usecase-*/{slug}.php` | `Version:` + `*_VERSION` only if `git diff <prev-tag>..HEAD -- plugins/<slug>/` is non-empty |
| Each `plugins/coverkit-usecase-*/readme.txt` | `Stable tag:` — same rule as bootstrap version |
| **All** plugin bootstraps + `readme.txt` | `Requires at least:` **7.0** and `Tested up to:` — **every release**, all plugins |

**WordPress compatibility (every release):**

1. Source of truth: [`package.json`](package.json) `wordpress.requiresAtLeast` (must stay **7.0**) and `wordpress.testedUpTo`.
2. Pass **`--update-wp-tested-up-to`** with `sync:version` on the release branch. It fetches the latest stable WordPress from `api.wordpress.org`, writes `wordpress.testedUpTo` to `package.json`, then propagates both fields to every plugin bootstrap (`Requires at least:`) and `readme.txt` (`Requires at least:` + `Tested up to:`).
3. Unchanged plugins still get compatibility header updates even when their semver is not bumped.

`--changed-since` uses `git diff` on the plugin folder only (not monorepo root files). New plugins under `plugins/` count as changed. Do **not** run plain `composer run sync:version` during release — that bumps every plugin.

Release zips use each plugin’s own `Version:` header for the filename (`<slug>-<plugin-version>.zip`), so unchanged plugins keep their prior zip name on the GitHub Release.

---

## CHANGELOG (release branch)

Use **today’s date** in local timezone (`YYYY-MM-DD`).

1. Rename `## [Unreleased]` → `## [<release>] — YYYY-MM-DD` (keep bullets).
2. **Do not** insert a fresh `## [Unreleased]` here — Phase 3 creates it on `develop` after merge.

---

## Verification (stop on failure)

Run from repo root in order:

| Step | Command |
| --- | --- |
| Packaging | `composer run package:release:verify` |
| PHP lint | `composer run lint:php` |
| PHP tests | `COVERKIT_PLUGIN_DIR=../coverkit composer run test:php` |

`package:release:verify` builds `dist/<slug>-<plugin-version>.zip` locally (version from each bootstrap `Version:` header) and asserts each zip contains `<slug>/<slug>.php`. Do **not** commit `dist/` or `build/release/` (gitignored; CI rebuilds on tag push).

**Scope warning:** Before commit, run `git status`. If files outside CHANGELOG/plugin sync paths changed, **warn** and only stage intended release files unless the user says otherwise.

---

## Checkpoint 1 — summary (required before commit/tag)

Present a compact summary:

- Branch: `release/<version>`
- Release version (already on `develop` — no monorepo bump on this branch)
- Loader sync and which plugins were version-synced vs left unchanged (from `git diff`)
- WordPress compatibility: `package.json` `wordpress.testedUpTo` and all plugins updated to `Requires at least: 7.0` + latest `Tested up to:`
- CHANGELOG: `## [Unreleased]` renamed to `## [<release>] — YYYY-MM-DD`
- Expected GitHub Release assets: `dist/coverkit-usecase-*-<plugin-version>.zip` (one per folder in `plugins/`; version per plugin header)
- Test / lint / packaging results (pass/fail)
- Files to be committed (list paths)
- Planned tag: `<version>`

**Ask explicitly:** “Proceed with commit and tag on `release/<version>`?”

If **no** → **stop**. Leave the release branch and local changes for the user to finish manually.

---

## Commit and tag (only after “yes”)

On **`release/<version>`**, stage release-intended files:

```bash
git add CHANGELOG.md coverkit-usecases.php package.json plugins/
git commit -m "release: <version>"
git tag <version>
```

- **Stop** if a commit hook fails.
- Do **not** push unless the user confirms in Checkpoint 2.

---

## Checkpoint 2 — push (optional)

Ask: “Push `release/<version>` and tag `<version>` to origin?”

If **yes**:

```bash
git push -u origin release/<version>
git push origin <version>
```

---

## Phase 3 — post-release bump on `develop`

Run only after:

1. User confirmed push in Checkpoint 2 (or tag exists locally and user skipped push intentionally), **and**
2. `release/<version>` is merged into `develop` (merge now if user confirms; otherwise **stop** until user confirms the PR is merged).

```bash
git checkout develop
git pull origin develop
# merge release/<version> if not already merged (user confirms):
# git merge release/<version>
```

Determine **next** version bump type:

- Default: **patch** (`npm version patch --no-git-tag-version`)
- User said `minor` or `major` in the original `/do-usecase-release` invocation → use that for Phase 3 only

```bash
NEXT=$(npm version patch --no-git-tag-version | tr -d v)   # or minor / major
```

Sync **loader only** (not all plugins):

```bash
composer run sync:version -- --loader-only
```

Do **not** pass `--all-plugins` or `--changed-since` in Phase 3 — only the monorepo loader version changes.

Insert empty `## [Unreleased]` at the top of [`CHANGELOG.md`](CHANGELOG.md).

```bash
git add package.json package-lock.json CHANGELOG.md coverkit-usecases.php
git commit -m "chore: bump to $NEXT for next development cycle"
```

- **Do not** tag `$NEXT`.
- **Checkpoint 3:** summary of bumped files + ask before push to `origin develop`.

If **yes**:

```bash
git push origin develop
```

---

## Post-release checklist (human)

After Phase 3, verify:

- [ ] GitHub Actions **Create Release** completed (unit tests → `package:release` → release assets)
- [ ] GitHub Release has one zip per `plugins/coverkit-usecase-*`
- [ ] Each zip extracts to `wp-content/plugins/<slug>/`
- [ ] `release/<version>` merged into `develop`
- [ ] Phase 3 bump landed on `develop` (`$NEXT` + fresh `## [Unreleased]`)

**Out of scope:** monorepo loader zip, README changelog sync, Freemius.

---

## Example invocation

User: **`/do-usecase-release`**

Agent: pre-flight on `develop` → `release = package.json` → `release/<version>` → sync loader/changed plugins (`--changed-since`) + CHANGELOG rename (no monorepo bump) → verify → summary → user “proceed” → commit + tag → optional push → merge → Phase 3 bump to next patch on `develop`.

User: **`/do-usecase-release minor`** — Phase 3 uses a **minor** bump for the next development version.

---

## One-time transition (2026-06-22)

Repos that shipped without a post-release bump (e.g. `0.1.3`) need a single Phase 3 on `develop` before the new workflow applies: bump to the next patch (loader via `--loader-only`) and ensure `## [Unreleased]` exists at the top of CHANGELOG. This was applied when the post-release workflow was adopted.
