---
name: create-coverkit-use-case
description: >-
  Guide discovery of CoverKit use case requirements, then scaffold a standalone
  WordPress plugin. Use when asked to create a CoverKit use case or custom image output.
---

# Create a CoverKit use case

Scaffold **one standalone WordPress plugin** that registers a custom CoverKit use case.

**Scaffold location:** create `coverkit-usecase-<kebab>/` in the **directory where the user invoked this skill** (workspace root or current working directory). Do **not** prepend `wp-content/plugins/` — the user’s IDE is already at the right place (often their WordPress `plugins/` folder, but not always).

**Requires:** the main [CoverKit](https://coverkit.com) plugin (`CoverKit\Use_Case`, `coverkit_register_use_case()` on `coverkit_init` priority 5).

**References (public docs — do not link to the private CoverKit GitHub repo):**

- [Custom use cases (developers)](https://docs.coverkit.com/user-guide/use-cases/custom-use-case/)
- [Use cases and output profiles](https://docs.coverkit.com/codebase/use-cases-and-output-profiles/)
- [Hooks and extension points](https://docs.coverkit.com/codebase/hooks-and-extension-points/)
- [How to configure use cases (editor)](https://docs.coverkit.com/user-guide/use-cases/how-to-configure/)

## Hard rule

**Do not create or edit plugin files until discovery is complete and the user confirms your summary.**

## Phase 0 — Discovery (required first)

Ask follow-up questions before scaffolding. Use the editor question UI when available (e.g. Cursor `AskQuestion`); otherwise ask as a short numbered list in one message.

### Core questions (always ask)

| # | Question | Why |
| --- | --- | --- |
| 1 | **What is this use case for?** — Describe the image output and where it will be used (e.g. email newsletter header, LinkedIn share, product card). | Drives label, slug, and purpose |
| 2 | **Image dimensions** — Fixed size? If yes, width × height (px). If unsure, say “use CoverKit defaults”. | `recommended_settings()` |
| 3 | **Editor only or front-end output?** — Preview/download in the template editor only, or also inject meta tags / replace featured images / other runtime hooks on the live site? | Label-only vs subclass + `init()` hooks |

### Follow-up questions (when relevant)

| Trigger | Question |
| --- | --- |
| Front-end output chosen | **Where should the image appear?** (e.g. `<meta property="og:image">`, featured image, custom action) |
| Custom dimensions | **Crop to exact size** or allow flexible aspect ratio? |
| Any use case | **Which WordPress fields should editors map?** (e.g. post title, author, featured image, ACF fields) — required vs optional |
| Complex editor needs | **Any settings toggles** for editors in the Use cases sidebar? (e.g. show badge, brand color) |
| Slug not obvious | **Preferred slug?** (short `snake_case` id, e.g. `email_header`) — or propose one from the description |
| Target folder unclear | **Where should the plugin folder be created?** Default: current directory → `coverkit-usecase-<kebab>/`. If the user wants a different parent (e.g. `wp-content/plugins/`), use that path and create missing parent folders first. |

### Rules for asking

- Batch questions in **one message** when possible.
- Skip follow-ups already answered in the user's first reply.
- If the user gave a rich description upfront, acknowledge it and only ask **gaps**.

## Phase 1 — Confirm before scaffold

Summarize in plain language:

- Use case **label** and proposed **slug** (`snake_case`)
- Dimensions, crop, and formats
- **Label-only** vs custom PHP **subclass** (and why)
- Field mappings (required / optional)
- Front-end behavior (if any)
- Target folder: `<base>/coverkit-usecase-<kebab>/` (default `<base>` = current directory)

Ask: **“Does this match what you need?”** Proceed only after yes (or user adjusts).

## Phase 2 — Derive names

From confirmed answers:

| Piece | Rule | Example (`email-header`) |
| --- | --- | --- |
| **kebab** | lowercase, hyphens | `email-header` |
| **snake** | underscores (CoverKit slug) | `email_header` |
| **studly** | PascalCase, no separators | `EmailHeader` |
| **wp_class** | studly words + `_Use_Case` | `Email_Header_Use_Case` |
| **plugin folder** | `coverkit-usecase-<kebab>` | `coverkit-usecase-email-header` |
| **namespace** | `CoverKitUseCase<Studly>` (subclass only) | `CoverKitUseCaseEmailHeader` |
| **text domain** | same as plugin folder | `coverkit-usecase-email-header` |
| **label** | user-facing title | `Email header` |

### Slug validation

| Check | Action if fail |
| --- | --- |
| **Invalid slug** | Reject characters outside `[a-z0-9_]` |
| **Built-in collision** | Do not use `sandbox`, `opengraph`, `featured_image`, or other CoverKit built-ins without explicit user confirmation |
| **Folder exists** | `<base>/coverkit-usecase-<kebab>/` already present → stop; suggest editing or pick another slug |

## Phase 3 — Scaffold

Create `<base>/coverkit-usecase-<kebab>/` where `<base>` is the confirmed directory (default: current working directory). Create `<base>` and any missing parent folders when the user chose a path that does not exist yet.

Default plugin version: **`1.0.0`** (user can override).

### Label-only vs subclass

| Approach | When |
| --- | --- |
| **Label only** | Editor preview only; CoverKit default dimensions and mappings are fine; no custom settings or front-end hooks |
| **Subclass `CoverKit\Use_Case`** | Custom dimensions, settings schema, mapping sources, or `init()` front-end hooks |

**Label-only** file layout:

```
coverkit-usecase-<kebab>/
├── coverkit-usecase-<kebab>.php
└── readme.txt
```

**Custom behavior** file layout:

```
coverkit-usecase-<kebab>/
├── coverkit-usecase-<kebab>.php
├── includes/class-<kebab>-use-case.php
└── readme.txt
```

### Bootstrap file (`coverkit-usecase-<kebab>.php`)

One file with a WordPress plugin header and registration on `coverkit_init` priority **5**. No version/path constants — use `plugin_dir_path( __FILE__ )` only when loading a subclass.

**Label only:**

```php
<?php
/**
 * Plugin Name: CoverKit Use Case: <Label>
 * Description: <One-line purpose from discovery>
 * Version: 1.0.0
 * Requires Plugins: coverkit
 * Text Domain: coverkit-usecase-<kebab>
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

add_action(
	'coverkit_init',
	static function (): void {
		\CoverKit\coverkit_register_use_case(
			'<snake>',
			array(
				'label' => __( '<Label>', 'coverkit-usecase-<kebab>' ),
			)
		);
	},
	5
);
```

**With custom subclass:**

```php
<?php
/**
 * Plugin Name: CoverKit Use Case: <Label>
 * Description: <One-line purpose from discovery>
 * Version: 1.0.0
 * Requires Plugins: coverkit
 * Text Domain: coverkit-usecase-<kebab>
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

add_action(
	'coverkit_init',
	static function (): void {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-<kebab>-use-case.php';

		\CoverKit\coverkit_register_use_case(
			'<snake>',
			array(
				'class' => \CoverKitUseCase<Studly>\<Wp_Class>::class,
				'label' => __( '<Label>', 'coverkit-usecase-<kebab>' ),
			)
		);
	},
	5
);
```

### Use case class (when subclass needed)

Follow patterns in [Use cases and output profiles](https://docs.coverkit.com/codebase/use-cases-and-output-profiles/) (registration, `recommended_settings()`, `use_case_mapping_sources()`, `init()`).

- Namespace = `CoverKitUseCase<Studly>`; extend `CoverKit\Use_Case`.
- Override `recommended_settings()` when custom dimensions/formats/crop were confirmed.
- Override `use_case_mapping_sources()` when custom required/recommended fields were confirmed.
- Override `use_case_settings_schema()` when editor toggles were confirmed (empty array if none).
- Override `init()` only for front-end hooks confirmed in discovery; keep it cheap.
- Use `\__()` with the plugin text domain for editor strings.

**Example `recommended_settings()`** (adjust width/height/crop from discovery):

```php
protected static function recommended_settings(): array {
	return array(
		'dimensions' => array(
			'width'  => 1200,
			'height' => 630,
		),
		'crop'       => true,
		'formats'    => array( 'jpg', 'webp' ),
	);
}
```

**Example `use_case_mapping_sources()`:**

```php
protected static function use_case_mapping_sources(): array {
	return array(
		'post_title' => array(
			'required' => true,
		),
		'site_logo'  => array(
			'recommended' => true,
		),
	);
}
```

**Example `init()`** (only when front-end output was confirmed):

```php
protected function init(): void {
	add_action( 'wp_head', array( $this, 'inject_meta' ) );
}
```

Implement hook methods on the subclass; validate output applies to the current request.

### readme.txt (optional)

Skip unless the user wants a WordPress.org–style readme. If included: match plugin name, `Stable tag: 1.0.0`, one-line changelog.

### Optional compiled assets

Only when the user explicitly needs JS/CSS — otherwise skip.

## Phase 4 — Finish

Tell the user:

1. Folder path where the plugin was created (`<base>/coverkit-usecase-<kebab>/`).
2. Activate **CoverKit Use Case: &lt;Label&gt;** in **Plugins** (requires CoverKit active).
3. Edit a CoverKit template → **Use cases** sidebar → enable the use case.
4. Map template shapes to the confirmed WordPress fields and preview.

Do **not** commit unless the user asks.

## Do not

- Scaffold before discovery and user confirmation.
- Put multiple use cases in one plugin.
- Prepend `wp-content/plugins/` to the scaffold path — create `coverkit-usecase-<kebab>/` directly in the confirmed base directory.
- Put multiple use cases in one plugin folder — each use case is its own **top-level** WordPress plugin directory.
- Add `define()` constants for version, file, or directory paths — keep the bootstrap minimal.
- Omit `Requires Plugins: coverkit` or registration on `coverkit_init` priority 5.
