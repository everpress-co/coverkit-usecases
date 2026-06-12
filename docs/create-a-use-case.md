# Create a use case

## Create with an IDE (no install)

For a **standalone** plugin on any WordPress site with CoverKit active, paste this in your IDE agent:

```text
Read this skill and create a CoverKit use case:

https://raw.githubusercontent.com/everpress-co/coverkit-usecases/main/SKILL.md
```

The skill asks follow-up questions (purpose, editor vs front-end output, field catalog), infers dimensions from your use case, confirms a summary, then scaffolds `coverkit-usecase-<slug>/` in the directory where you invoked it (usually your WordPress `plugins/` folder). See [root `SKILL.md`](../SKILL.md).

---

## Monorepo path

Step-by-step guide to add a custom CoverKit use case in this repository.

### Prerequisites

- [CoverKit](https://coverkit.com) installed and active
- This repo cloned to `wp-content/plugins/coverkit-usecases/`
- **CoverKit Use Cases** (root plugin) activated for monorepo development

### Quick path (copy starter)

1. Copy the starter plugin:

   ```bash
   cp -R plugins/coverkit-usecase-starter plugins/coverkit-usecase-my-slug
   ```

2. Rename the bootstrap file to `coverkit-usecase-my-slug.php`.

3. Replace starter-specific names (namespace, constants, slug, label, text domain) throughout the folder.

4. Register your CoverKit slug on `coverkit_init` (priority 5). Example slug: `my_slug` (snake_case).

5. Add a row to the **Use cases** table in [`README.md`](../README.md).

6. Run `composer run lint:php` and `COVERKIT_PLUGIN_DIR=../coverkit composer run test:php`.

### Label-only vs subclass

| Approach | When to use |
| --- | --- |
| **Label only** | Default dimensions and field catalog are fine; no front-end hooks. |
| **Subclass `CoverKit\Use_Case`** | Custom dimensions, settings schema, mapping sources, or runtime hooks. |

See [`architecture.md`](architecture.md) and CoverKit [custom use case docs](https://docs.coverkit.com/user-guide/use-cases/custom-use-case/) ([output profiles](https://docs.coverkit.com/codebase/use-cases-and-output-profiles/)).

### Plugin header

Every bootstrap under `plugins/coverkit-usecase-*/` must include a full WordPress plugin header (`Plugin Name`, `Version`, `Requires Plugins: coverkit`, `Text Domain`, etc.) so release zips install as standalone plugins.

Use `plugins/coverkit-usecase-starter/coverkit-usecase-starter.php` as the template.

Set `Version:` and `Stable tag:` to the current value in root [`package.json`](../package.json). At release time, `/do-usecase-release` bumps the monorepo version and `composer run sync:version` updates every plugin automatically.

### Optional compiled assets

If your use case needs JS/CSS, add a `package.json` with `@wordpress/scripts` in the plugin folder. Release packaging runs `npm run build` and ships `build/` (not `src/`). See [`architecture.md`](architecture.md#release-packaging).

### Enable in the editor

1. Reload the site (monorepo loader picks up new folders automatically).
2. Edit a CoverKit template → **Use cases** sidebar → enable your use case.
3. Map fields and preview.

### Agent scaffolding (this repo)

In Cursor, run **`/new-usecase <slug>`** or ask an agent to read [`.cursor/skills/new-usecase/SKILL.md`](../.cursor/skills/new-usecase/SKILL.md). For standalone sites, use the [root `SKILL.md`](../SKILL.md) URL prompt instead.
