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
- [Community use case plugins (examples)](https://github.com/everpress-co/coverkit-usecases/tree/main/plugins)

## Explore existing examples (do this first)

Before discovery questions, browse **public community plugins** and CoverKit’s documented patterns so you know what can be built and which approach fits.

### Community plugins (public repo)

**Listing:** [github.com/everpress-co/coverkit-usecases/tree/main/plugins](https://github.com/everpress-co/coverkit-usecases/tree/main/plugins)

1. List every `coverkit-usecase-*` folder (the repo grows over time — do not rely on a fixed list).
2. For each folder, read the bootstrap PHP and optional `includes/class-*-use-case.php`.
3. If the repo is not in the workspace, fetch files via raw GitHub URLs:

   `https://raw.githubusercontent.com/everpress-co/coverkit-usecases/main/plugins/<folder>/<filename>`

**Known example today** (verify against the listing — more may exist):

| Plugin folder | Slug | What it demonstrates |
| --- | --- | --- |
| `coverkit-usecase-starter` | `starter` | Subclass with custom dimensions (400×400), editor settings toggle, mapping sources, **no** front-end hooks |

Use these plugins as **scaffold patterns**, not copy-paste sources — adapt names, dimensions, and behavior to the user’s request.

### Built-in CoverKit patterns (via public docs)

CoverKit ships built-in use cases that show advanced patterns. Read about them in [Custom use cases](https://docs.coverkit.com/user-guide/use-cases/custom-use-case/) and [Use cases overview](https://docs.coverkit.com/user-guide/use-cases/) — do **not** link to the private CoverKit GitHub repo.

| Built-in | Typical purpose | Patterns to learn |
| --- | --- | --- |
| **Open Graph** | Social share previews | Fixed 1200×630, editor toggles (post types, front page, archives), `wp_head` meta injection |
| **Featured image** | Replace post thumbnails | Canvas-native sizing, front-end filter hooks, post-type scoping |
| **Sandbox** | Editor experimentation | Full settings schema (text, toggle, textarea), mapping catalog |

If CoverKit is installed locally (`wp-content/plugins/coverkit/`), you may read `includes/use-cases/` for implementation detail — still do not link to private repos in generated plugin headers or user-facing text.

### Inspiration to suggest during discovery

When the user’s goal is vague, offer concrete ideas grounded in the examples above:

| Category | Examples |
| --- | --- |
| **Social / meta images** | LinkedIn share (1200×627), Pinterest pin (1000×1500), X/Twitter card, custom `og:image` variant |
| **Editor-only presets** | Newsletter header, email banner, PDF/export preview, branded download size — preview in template editor only |
| **Front-end integration** | Custom meta tags, alternate featured image, category/archive hero, WooCommerce product card |
| **Vertical formats** | Podcast cover (3000×3000), YouTube thumbnail (1280×720), story/reel (1080×1920), event poster |

Match complexity to need: **label-only** registration when defaults suffice; **subclass** when output settings, editor settings, mappings, or runtime hooks differ.

## Hard rule

**Do not create or edit plugin files until discovery is complete and the user confirms your summary.**

## Phase 0 — Discovery (required first)

Ask follow-up questions before scaffolding. Use the editor question UI when available (e.g. Cursor `AskQuestion`); otherwise ask as a short numbered list in one message.

### Core questions (always ask)

| # | Question | Why |
| --- | --- | --- |
| 1 | **What is this use case for?** — Describe the image output and where it will be used (e.g. email newsletter header, LinkedIn share, product card). | Drives label, slug, purpose, and inferred output settings |
| 2 | **Editor only or front-end output?** — Preview/download in the template editor only, or also inject meta tags / replace featured images / other runtime hooks on the live site? | Label-only vs subclass + `init()` hooks |
| 3 | **Plugin author metadata** — **Author** name, **Author URI** (your site), and optional **Plugin URI** (project/repo link). Suggest values you can infer (see below); user confirms or overrides. | WordPress plugin header |

### Follow-up questions (when relevant)

| Trigger | Question |
| --- | --- |
| Front-end output chosen | **Where should the image appear?** (e.g. `<meta property="og:image">`, featured image, custom action) |
| Any use case | **Which WordPress fields should editors map?** (e.g. post title, author, featured image, ACF fields) — required vs optional |
| Complex editor needs | **Any settings toggles** for editors in the Use cases sidebar? (e.g. show badge, brand color) |
| Slug not obvious | **Preferred slug?** (short `snake_case` id, e.g. `email_header`) — or propose one from the description |
| Target folder unclear | **Where should the plugin folder be created?** Default: current directory → `coverkit-usecase-<kebab>/`. If the user wants a different parent (e.g. `wp-content/plugins/`), use that path and create missing parent folders first. |
| Output size ambiguous | **Dimensions, crop, or formats** — only when the use case description does not imply a standard size (see below). Propose sensible values first; ask to confirm or override. |

### Infer output settings (do not lead with dimensions)

Decide **dimensions, crop, and formats** from the described use case and examples in **Explore existing examples**. Ask only when unclear or the user wants something non-standard.

| Use case type | Typical inference |
| --- | --- |
| Open Graph / Facebook / generic social share | 1200×630, crop, `jpg` + `webp` |
| LinkedIn share | 1200×627, crop |
| Pinterest pin | 1000×1500, crop |
| X / Twitter card | 1200×675 or platform default, crop |
| YouTube thumbnail | 1280×720, crop |
| Podcast / album cover | 3000×3000, crop |
| Story / reel / vertical social | 1080×1920, crop |
| Featured-image-style / flexible layout | No fixed dimensions — canvas-native (formats only) |
| Editor-only export / unknown format | CoverKit defaults — often **label-only** with no custom `recommended_settings()` |

When inferring, state your proposal in the Phase 1 summary (“LinkedIn share → 1200×627, cropped”) so the user can correct it — do not block discovery on pixel-perfect answers upfront.

### Suggested author metadata (propose, then confirm)

Infer when possible; always let the user override.

| Field | Where to look | Example fallback |
| --- | --- | --- |
| **Author** | `git config user.name`, workspace/site branding, user’s first reply | Ask if nothing reliable |
| **Author URI** | Site URL in the project (`WP_HOME`, `siteurl` in config, package.json `homepage`, README links) | Ask if unknown |
| **Plugin URI** | User’s repo URL, project homepage, or omit | Leave empty if user has no preference |

### Rules for asking

- Batch questions in **one message** when possible.
- Skip follow-ups already answered in the user's first reply.
- If the user gave a rich description upfront, acknowledge it and only ask **gaps**.
- When the request is vague, suggest 2–3 concrete use case ideas from **Explore existing examples** before asking core questions.
- Do **not** ask for dimensions, crop, or formats upfront — infer them from the use case description; ask only if ambiguous.

## Phase 1 — Confirm before scaffold

Summarize in plain language:

- Use case **label** and proposed **slug** (`snake_case`)
- **Proposed** dimensions, crop, and formats (inferred from the use case — user can adjust)
- **Label-only** vs custom PHP **subclass** (and why)
- Field mappings (required / optional)
- Front-end behavior (if any)
- Target folder: `<base>/coverkit-usecase-<kebab>/` (default `<base>` = current directory)
- Plugin **Author**, **Author URI**, and **Plugin URI** (if any)

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
| **author** | confirmed Author header | `Jane Doe` |
| **author_uri** | confirmed Author URI | `https://example.com` |
| **plugin_uri** | confirmed Plugin URI or empty | `https://github.com/janedoe/coverkit-usecase-email-header` |

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
| **Label only** | Editor preview only; CoverKit default output settings and mappings are fine; no custom settings or front-end hooks |
| **Subclass `CoverKit\Use_Case`** | Custom output settings, settings schema, mapping sources, or `init()` front-end hooks |

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
 * Plugin URI: <plugin_uri or omit this line>
 * Description: <One-line purpose from discovery>
 * Version: 1.0.0
 * Requires Plugins: coverkit
 * Author: <author>
 * Author URI: <author_uri>
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
 * Plugin URI: <plugin_uri or omit this line>
 * Description: <One-line purpose from discovery>
 * Version: 1.0.0
 * Requires Plugins: coverkit
 * Author: <author>
 * Author URI: <author_uri>
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

Follow patterns in [Use cases and output profiles](https://docs.coverkit.com/codebase/use-cases-and-output-profiles/) and mirror structure from [community plugins](https://github.com/everpress-co/coverkit-usecases/tree/main/plugins) (e.g. `coverkit-usecase-starter`).

- Namespace = `CoverKitUseCase<Studly>`; extend `CoverKit\Use_Case`.
- Override `recommended_settings()` when the inferred output profile differs from CoverKit defaults (fixed dimensions, crop, or formats).
- Override `use_case_mapping_sources()` when custom required/recommended fields were confirmed.
- Override `use_case_settings_schema()` when editor toggles were confirmed (empty array if none).
- Override `init()` only for front-end hooks confirmed in discovery; keep it cheap.
- Use `\__()` with the plugin text domain for editor strings.

**Example `recommended_settings()`** (use inferred width/height/crop/formats from the use case):

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
