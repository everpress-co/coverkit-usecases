---
description: Prepare and cut a coverkit-usecases monorepo release — bump version first (default patch), sync changed plugins, release branch and tag, then restore ## [Unreleased] on develop
---

# Prepare and cut a CoverKit Use Cases release

Run a **three-phase** release workflow. **Bump the monorepo version first** (default **patch**), then ship that version. **Stop on any failure** — do not commit, tag, or push through red tests or an empty `## [Unreleased]` without calling it out and getting explicit user direction.

**Changelog-first:** Day-to-day work updates [`CHANGELOG.md`](CHANGELOG.md) under `## [Unreleased]`. Version files on `develop` track the **last shipped** release. This command **bumps** to the release version first, renames `## [Unreleased]` to that version, syncs changed plugins, and tags. **Phase 3** only restores an empty `## [Unreleased]` on `develop` after merge — **no** post-release version bump.

## Overview

```text
develop (version = last shipped, ## [Unreleased] has bullets)
  → pre-flight on develop
  → bump monorepo version first (default patch, or minor/major/x.y.z)
  → release/<version> — sync changed plugins, CHANGELOG rename
  → package:release:verify + tests
  → checkpoint → commit + tag
  → checkpoint → optional push
  → merge release/<version> → develop (user confirms)
  → Phase 3: fresh ## [Unreleased] on develop (no version bump)
```

Pushing the tag triggers [`.github/workflows/release.yml`](.github/workflows/release.yml) (unit tests, `package:release`, one install-ready zip per use case on the GitHub Release). Tag format: **`<version>`** — semver **without** a `v` prefix (e.g. `0.1.1`, not `v0.1.1`).

### Slash-command arguments

Parse the first argument after `/do-usecase-release` (optional):

| Argument | Release version |
| --- | --- |
| *(none)* | **patch** bump of current `package.json` |
| `patch` | same as default |
| `minor` | minor bump |
| `major` | major bump |
| `x.y.z` | exact semver — set `package.json` (and loader) to that value |

There is **no** separate post-ship next-version bump. The next `/do-usecase-release` bumps again from the version just shipped.

---

## Phase 1 — Pre-flight on `develop` (hard stops)

Run **before** creating the release branch. Do **not** auto-merge feature branches into `develop`.

| Check | Action if fail |
| --- | --- |
| **Current branch** | Must be **`develop`**. If on `feature/*`, `release/*`, or anything else → **stop**. Instruct: merge/rebase into `develop` first. |
| **Sync `develop`** | `git fetch origin`. Compare `develop` to `origin/develop`; `git pull origin develop` if behind (warn if offline / fetch fails). |
| **Working tree** | Prefer a **clean** tree on `develop`. If uncommitted changes exist → **stop** and show `git status -sb`. Continue only if the user **explicitly** accepts releasing with those changes. |

Then **resolve and bump release version** (next section). After the bump:

| Check | Action if fail |
| --- | --- |
| **Duplicate branch/tag** | After fetch: `release/<version>` must not exist locally or on `origin`. `git tag -l '<version>'` must be empty locally; check remote tags after fetch. → **stop**. |
| **Version file consistency** | After bump + loader sync: [`package.json`](package.json) `"version"` must match [`coverkit-usecases.php`](coverkit-usecases.php) `Version:` and `COVERKIT_USECASES_VERSION`. → **stop** on drift. |
| **CHANGELOG ready** | `## [Unreleased]` must have **at least one** bullet (`-` lines). Empty section → **stop** before branching. |

---

## Resolve and bump release version (first version step)

Run on **`develop`** after pre-flight branch/sync/tree checks, **before** creating `release/<version>`.

1. Read **current** from [`package.json`](package.json): `CURRENT=$(jq -r '.version' package.json)`.
2. Verify current matches [`coverkit-usecases.php`](coverkit-usecases.php) `Version:` / `COVERKIT_USECASES_VERSION` — **stop** on drift (fix before bumping).
3. Parse the slash-command argument (see [Slash-command arguments](#slash-command-arguments)).
4. Compute and apply `<release>`:

   ```bash
   # default / patch
   RELEASE=$(npm version patch --no-git-tag-version | tr -d v)

   # minor / major — same pattern with npm version minor|major

   # exact x.y.z
   RELEASE=0.4.0
   npm version "$RELEASE" --no-git-tag-version
   ```

5. Sync the **loader only** so the bootstrap matches `package.json` immediately:

   ```bash
   composer run sync:version -- --loader-only
   ```

6. Echo: `Releasing <release> (bumped from <current>).`
7. Re-check duplicate branch/tag for `<release>` after the bump.

**Do not** defer the monorepo bump to after tag or push. Per-plugin semver sync for **changed** plugins still happens on the release branch (next section).

---

## Phase 2 — Create `release/<version>` branch

Still on **`develop`**, after pre-flight and version bump succeed:

```bash
git checkout develop
git pull origin develop   # if network OK; else warn and continue with local develop
git checkout -b release/<version>
```

Carry uncommitted version bumps onto the release branch. All release edits, tests, commits, and tags happen **on this branch**, not on `develop`.

---

## Sync changed plugin versions

[`package.json`](package.json) and the loader already equal `<release>` from the bump step.

**Per-use-case plugin versions** — sync **only when that plugin changed** since the previous release so changed plugins match the monorepo version. Unchanged plugins keep their existing `Version:` / `Stable tag:`.

1. Resolve previous tag after `git fetch --tags`:

   ```bash
   PREV_TAG=$(git describe --tags --abbrev=0 2>/dev/null || true)
   ```

   - If `PREV_TAG` is empty (first release), sync all plugins.
   - Otherwise use `PREV_TAG` as the diff base (e.g. `0.1.1`).
   - If `PREV_TAG` equals the new `<release>` (should not happen after a real bump), **stop** — tag already exists.

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

**Scope warning:** Before commit, run `git status`. If files outside CHANGELOG/plugin sync/version paths changed, **warn** and only stage intended release files unless the user says otherwise.

---

## Checkpoint 1 — summary (required before commit/tag)

Present a compact summary:

- Branch: `release/<version>`
- Release version (bumped from `<current>` → `<release>`; argument used)
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
git add CHANGELOG.md coverkit-usecases.php package.json package-lock.json plugins/
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

## Phase 3 — restore `## [Unreleased]` on `develop`

Run only after:

1. User confirmed push in Checkpoint 2 (or tag exists locally and user skipped push intentionally), **and**
2. `release/<version>` is merged into `develop` (merge now if user confirms; otherwise **stop** until user confirms the PR is merged).

```bash
git checkout develop
git pull origin develop
# merge release/<version> if not already merged (user confirms):
# git merge release/<version>
```

**Do not** run `npm version` or `composer run sync:version` for a next-cycle bump. `develop` stays on `<release>` (the version just shipped).

Insert an empty `## [Unreleased]` at the top of [`CHANGELOG.md`](CHANGELOG.md) if it is missing after the merge.

```bash
git add CHANGELOG.md
git commit -m "chore: open Unreleased section for next development cycle"
```

- Skip the commit if `## [Unreleased]` already exists and nothing else changed.
- **Checkpoint 3:** summary + ask before push to `origin develop`.

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
- [ ] `develop` version files remain at `<release>` (no post-ship bump)
- [ ] Fresh `## [Unreleased]` on `develop`

**Out of scope:** monorepo loader zip, README changelog sync, Freemius.

---

## Example invocation

User: **`/do-usecase-release`**

Agent: pre-flight on `develop` → **bump patch** → `release/<version>` → sync loader/changed plugins (`--changed-since`) + CHANGELOG rename → verify → summary → user “proceed” → commit + tag → optional push → merge → Phase 3 opens `## [Unreleased]` only.

User: **`/do-usecase-release minor`** — bump minor first, then ship that version.

User: **`/do-usecase-release 0.2.0`** — set monorepo version to `0.2.0`, then ship.

---

## Transition note

Under the previous workflow, **Phase 3** bumped `develop` to the *next* version after each release. Now version files on `develop` stay at the **last shipped** release until the next `/do-usecase-release` bumps again.

If `package.json` is already ahead of the latest git tag and you want to **ship that version without another bump**, pass it explicitly (exact current). Default `/do-usecase-release` always patch-bumps.
