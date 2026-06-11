---
name: understand-use-cases
description: >-
  Onboarding for the coverkit-usecases monorepo: loader vs standalone install,
  coverkit_register_use_case API, label-only vs subclass, built-in slugs to
  avoid, and links to CoverKit reference classes.
disable-model-invocation: true
---

# Understand CoverKit use cases (this repo)

Use when onboarding to the repo, reviewing architecture, or deciding how to implement a custom use case.

## Repo layout

```
coverkit-usecases/
├── coverkit-usecases.php              # Monorepo loader (activate in dev)
├── plugins/coverkit-usecase-<slug>/   # One WordPress plugin per use case
└── tests/php/                         # PHPUnit for this repo only
```

## Monorepo vs standalone

| Context | How it loads |
| --- | --- |
| **Monorepo dev** | Clone into `wp-content/plugins/coverkit-usecases`, activate **CoverKit Use Cases**. Root loader `require_once`s each bootstrap on `plugins_loaded` — WordPress does not scan nested `plugins/`. |
| **Release zip** | Download from [Releases](https://github.com/everpress-co/coverkit-usecases/releases), extract to `wp-content/plugins/coverkit-usecase-<slug>/`, activate like any plugin. Each zip has a full WP plugin header and `Requires Plugins: coverkit`. |

## Registration API

On `coverkit_init` (priority **5**, before registry boot at 10):

```php
\CoverKit\coverkit_register_use_case( 'my_slug', array(
    'label' => __( 'My use case', 'coverkit-usecase-my-slug' ),
    // Optional:
    'class' => My_Use_Case::class,
) );
```

- **`label` only** — inherits base `CoverKit\Use_Case` behavior (dimensions, mappings, settings from defaults).
- **`class`** — subclass `CoverKit\Use_Case` for `recommended_settings()`, `use_case_mapping_sources()`, `use_case_settings_schema()`, and `init()` hooks.

Defer `require_once` of subclass files until inside the `coverkit_init` callback so `Use_Case` is loaded.

## Built-in slugs (avoid collisions)

CoverKit ships built-ins such as `sandbox`, `opengraph`, `featured_image`. Custom slugs should be distinct (e.g. `email_header`, `pinterest_board`).

## CoverKit reference

- Base class: `CoverKit\Use_Case` in main plugin `includes/class-coverkit-use-case.php`
- Registry: `CoverKit\Use_Case_Registry`
- User guide: [Custom use cases](https://github.com/everpress-co/coverkit/blob/develop/docs/src/content/docs/user-guide/use-cases/custom-use-case.md)
- Example in main plugin: `includes/use-cases/class-coverkit-minimal-use-case.php`

## Docs in this repo

- [`docs/create-a-use-case.md`](../../../docs/create-a-use-case.md) — step-by-step
- [`docs/architecture.md`](../../../docs/architecture.md) — loader, headers, subclass patterns
- [`docs/agents.md`](../../../docs/agents.md) — skills and agent entry points

## Scaffolding

Use **`/new-usecase <slug>`** or read [`.cursor/skills/new-usecase/SKILL.md`](../new-usecase/SKILL.md).
