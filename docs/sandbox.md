---
title: Sandbox
description: Test templates, mappings, and live editor previews with every built-in setting control—no public front-end output.
---
The **Sandbox** use case is CoverKit’s reference implementation for the template editor. It exercises the full mapping pipeline, **live preview while editing**, and every built-in settings control type. It does **not** register front-end hooks (no Open Graph meta tags, no thumbnail replacement, and no other public output).

Sandbox ships with the **CoverKit Use Cases** plugin (`plugins/coverkit-sandbox/` in this repository). Activate **CoverKit** and **CoverKit Use Cases** in WordPress.

Use **Sandbox** to validate layouts and field mappings safely. For production outputs, enable Open Graph image or Featured image in CoverKit core instead.

## When to enable it

- **Editor testing** — check layout and mappings with live preview before turning on public outputs
- **Extension development** — study a complete settings schema and mapping catalog when building custom use cases
- **Training** — walk editors through controls without affecting the live site

## Recommended output

| | |
| --- | --- |
| **Recommended size** | 300 × 300 px (square, cropped) |
| **Formats** | JPG and WebP are recommended |

If your template canvas is not square, output is still scaled and cropped to the recommended square.

## Settings

Sandbox merges shared image settings, post-type controls, and use-case-specific fields so you can see every built-in control type in the editor.

| Setting | Control | Purpose |
| --- | --- | --- |
| **Format** | Select | Output file format (PNG, JPG, WebP, GIF). |
| **Quality** | Range | JPG quality (shown when format is JPG). |
| **Post types** | Checkbox list | Which content types are included when resolving preview data (defaults to **Post**). |
| **Alt text** | Text | Alt text for the preview image. |
| **Caption** | Textarea | Optional caption text where the UI supports it. |
| **Show border** | Toggle | Draw a border around the preview. |
| **Include metadata** | Checkbox | Include extra metadata in sandbox output when enabled. |
| **Front page** | Toggle | Allow this assignment on the front page (separate from the post type list). |
| **Archives and search** | Toggle | Allow this assignment on archives and search (not singular, not the front page). |

## Required and recommended field bindings

- **Required:** **Post title** → bind to a text layer.
- **Recommended:** **Featured image**, **Post link**, **Post excerpt**, **Author**, **Site logo** — bind when your design uses them.

The Sandbox implementation demonstrates field formatting via the `coverkit_use_case_sandbox_format_field_value` filter (for example, a demo prefix on **Post date** when that field is bound).

## Verify

1. Enable **Sandbox** on the template and save.
2. Bind **Post title** (and other fields you need).
3. Confirm the **live preview** updates while you edit bindings and settings in the CoverKit sidebar.

## QA fixture

See [`plugins/coverkit-sandbox/fixtures/block-bindings-sandbox-qa.html`](../plugins/coverkit-sandbox/fixtures/block-bindings-sandbox-qa.html) for block-bindings manual QA markup.
