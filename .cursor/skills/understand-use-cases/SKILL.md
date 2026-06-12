---
name: understand-use-cases
description: >-
  CoverKit custom use case architecture: coverkit_register_use_case API,
  label-only vs subclass, built-in slugs to avoid, and docs.coverkit.com
  references. Use when onboarding or reviewing how use case plugins work.
disable-model-invocation: true
---

# Understand CoverKit use cases

Use when explaining how custom use case plugins work, reviewing architecture, or deciding label-only vs subclass.

## One plugin = one use case

Each custom use case is a **standalone WordPress plugin** in `wp-content/plugins/coverkit-usecase-<slug>/` with `Requires Plugins: coverkit`. Site owners install and activate it like any other plugin.

To scaffold a new use case, use [root `SKILL.md`](../../../SKILL.md) (paste the URL prompt from [`README.md`](../../../README.md)) — not this file.

## Registration API

On `coverkit_init` (priority **5**, before registry boot at 10):

```php
\CoverKit\coverkit_register_use_case( 'my_slug', array(
    'label' => __( 'My use case', 'coverkit-usecase-my-slug' ),
    // Optional:
    'class' => My_Use_Case::class,
) );
```

- **`label` only** — inherits base `CoverKit\Use_Case` behavior (dimensions, field catalog, settings from defaults). Editors bind fields on template layers via native block bindings.
- **`class`** — subclass `CoverKit\Use_Case` for `recommended_settings()`, `use_case_mapping_sources()`, `use_case_settings_schema()`, and `init()` hooks.

Defer `require_once` of subclass files until inside the `coverkit_init` callback so `Use_Case` is loaded.

## Built-in slugs (avoid collisions)

CoverKit ships built-ins such as `sandbox`, `opengraph`, `featured_image`. Custom slugs should be distinct (e.g. `email_header`, `pinterest_board`).

## CoverKit reference (public docs)

- [Custom use cases (developers)](https://docs.coverkit.com/user-guide/use-cases/custom-use-case/)
- [Use cases and output profiles](https://docs.coverkit.com/codebase/use-cases-and-output-profiles/)
- [Hooks and extension points](https://docs.coverkit.com/codebase/hooks-and-extension-points/)
- Base class: `CoverKit\Use_Case`; registry: `CoverKit\Use_Case_Registry`

## Scaffolding

- **Any WordPress site:** [root `SKILL.md`](../../../SKILL.md) URL prompt (discovery Q&A, then scaffold under `wp-content/plugins/`).
- **Maintainers in this repository:** `/new-usecase <slug>` or [`.cursor/skills/new-usecase/SKILL.md`](../new-usecase/SKILL.md).
