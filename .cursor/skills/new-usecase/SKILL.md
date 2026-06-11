---
name: new-usecase
description: >-
  Scaffold plugins/coverkit-usecase-<slug>/ with a full WordPress plugin header,
  coverkit_init registration, and optional Use_Case subclass. Use when the user
  invokes /new-usecase or asks to add a custom CoverKit use case in this repo.
disable-model-invocation: true
---

# New CoverKit use case

Scaffold **one new plugin** under `plugins/`. Each plugin = one registered use case. Requires the main CoverKit plugin (`CoverKit\Use_Case`, `coverkit_register_use_case()` on `coverkit_init` priority 5).

**Reference:** `plugins/coverkit-usecase-starter/` and CoverKit [custom use case docs](https://github.com/everpress/coverkit/blob/develop/docs/src/content/docs/user-guide/use-cases/custom-use-case.md).

## When to run

User invokes **`/new-usecase`** or asks to add/create/scaffold a custom use case in this repo.

## Hard stops

| Check | Action if fail |
| --- | --- |
| **No slug/name** | Ask once for a short slug (e.g. `email-header`, `pinterest`). |
| **Slug exists** | `plugins/coverkit-usecase-<kebab>/` already present → stop; suggest editing or pick another slug. |
| **Invalid slug** | Reject spaces-only, reserved `starter`, or characters outside `[a-z0-9-]`. |

## Phase 1 — Derive names

From user input, derive:

| Piece | Rule | Example (`email-header`) |
| --- | --- | --- |
| **kebab** | lowercase, hyphens | `email-header` |
| **snake** | underscores (CoverKit slug) | `email_header` |
| **studly** | PascalCase, no separators | `EmailHeader` |
| **wp_class** | studly words + `_Use_Case` | `Email_Header_Use_Case` |
| **plugin folder** | `coverkit-usecase-<kebab>` | `coverkit-usecase-email-header` |
| **namespace** | `CoverKitUseCase<Studly>` | `CoverKitUseCaseEmailHeader` |
| **text domain** | same as plugin folder | `coverkit-usecase-email-header` |
| **const prefix** | `COVERKIT_USECASE_<SNAKE_UPPER>_` | `COVERKIT_USECASE_EMAIL_HEADER_` |
| **register fn** | `coverkit_usecase_<snake>_register` | `coverkit_usecase_email_header_register` |
| **label** | user-facing; default title-case from kebab | `Email header` |

Confirm **label**, **dimensions**, and **editor-only vs front-end hooks** only when ambiguous. Default: **editor-only** (base `Use_Case` behavior), **400×400 crop**, `post_title` required — only scaffold a PHP subclass when the user needs custom dimensions, settings, mappings, or front-end hooks.

## Phase 2 — Create plugin files

Copy structure from `plugins/coverkit-usecase-starter/` and replace all starter-specific names.

**Label-only use case** (base CoverKit behavior, no custom PHP class):

```
plugins/coverkit-usecase-<kebab>/
├── coverkit-usecase-<kebab>.php    # bootstrap + coverkit_register_use_case()
└── readme.txt
```

**Custom behavior** (settings, dimensions, mappings, or hooks):

```
plugins/coverkit-usecase-<kebab>/
├── coverkit-usecase-<kebab>.php
├── includes/class-<kebab>-use-case.php
└── readme.txt
```

### Bootstrap file (`coverkit-usecase-<kebab>.php`)

Every bootstrap is a **valid standalone WordPress plugin** with a complete header block:

```php
/**
 * Plugin Name: CoverKit Use Case: <Label>
 * Plugin URI: https://github.com/everpress/coverkit-usecases
 * Description: <One-line purpose>
 * Version: 0.1.0
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Requires Plugins: coverkit
 * Author: EverPress
 * Author URI: https://coverkit.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coverkit-usecase-<kebab>
 *
 * @package CoverKitUseCase<Studly>
 */
```

- `declare(strict_types=1);`, `defined( 'ABSPATH' ) || exit;`
- Version `0.1.0`; define `VERSION`, `FILE`, `DIR` constants with const prefix above.
- Register on `coverkit_init` priority **5** only (not `plugins_loaded`). `Requires Plugins: coverkit` ensures CoverKit is active.
- Use a `coverkit_usecase_<snake>_register()` function hooked to `coverkit_init`; defer `require_once` of subclass files inside that function.

Label only:

```php
function coverkit_usecase_<snake>_register(): void {
    \CoverKit\coverkit_register_use_case(
        '<snake>',
        array(
            'label' => __( '<Label>', '<text-domain>' ),
        )
    );
}

add_action( 'coverkit_init', 'coverkit_usecase_<snake>_register', 5 );
```

With custom subclass:

```php
function coverkit_usecase_<snake>_register(): void {
    require_once COVERKIT_USECASE_<SNAKE_UPPER>_DIR . 'includes/class-<kebab>-use-case.php';

    \CoverKit\coverkit_register_use_case(
        '<snake>',
        array(
            'class' => \<Namespace>\<Wp_Class>::class,
            'label' => __( '<Label>', '<text-domain>' ),
        )
    );
}

add_action( 'coverkit_init', 'coverkit_usecase_<snake>_register', 5 );
```

### Use case class (optional)

Skip when label-only registration is enough. When needed:

- Namespace = namespace above; extend `CoverKit\Use_Case`.
- Implement `recommended_settings()`, `use_case_mapping_sources()`, `use_case_settings_schema()` (minimal or empty), `init()` (front-end hooks only when requested).
- Use `\__()` with the plugin text domain for editor strings.
- Align array `=>` per PHPCS (see starter).

### readme.txt

- Match plugin name/description; `Stable tag: 0.1.0`; changelog entry for initial release.

## Phase 3 — Wire up repo

1. Add a row to the **Use cases** table in root [`README.md`](../../../README.md).
2. Run `composer run docs:skills` to refresh the Agent skills table.
3. Run `composer run lint:php` from repo root and fix any PHPCS issues.

No symlink or plugin re-activation needed — root [`coverkit-usecases.php`](../../../coverkit-usecases.php) auto-loads new folders under `plugins/`.

## Phase 4 — Summarize for user

Tell the user:

- Folder path under `plugins/`
- Use case slug and label to find in the CoverKit template editor
- Monorepo: activate **CoverKit** and **CoverKit Use Cases**; standalone zip: activate only the use case plugin (requires CoverKit)

Do **not** commit unless the user asks.

## Do not

- Put multiple use cases in one plugin.
- Register on a slug that collides with CoverKit built-ins (`sandbox`, `opengraph`, `featured_image`, etc.) without user confirmation.
- Mutate `coverkit-usecase-starter` when scaffolding — copy it.
- Omit the WordPress plugin header — every bootstrap must be installable as a top-level plugin.
