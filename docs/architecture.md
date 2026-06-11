# Architecture

How the CoverKit use cases monorepo is organized.

## Components

```mermaid
flowchart LR
  subgraph repo [coverkit-usecases]
    Loader[coverkit-usecases.php]
    UC1[coverkit-usecase-starter]
    UCn[coverkit-usecase-*]
    Loader -->|require bootstrap| UC1
    Loader --> UCn
  end
  CoverKit[CoverKit plugin] -->|coverkit_init| UC1
  CoverKit --> UCn
```

| Piece | Role |
| --- | --- |
| **`coverkit-usecases.php`** | Monorepo loader; `glob()` + `require_once` for each `plugins/coverkit-usecase-*` bootstrap |
| **`plugins/coverkit-usecase-<slug>/`** | One WordPress plugin = one registered use case |
| **CoverKit** | Provides `coverkit_register_use_case()`, `Use_Case` base class, editor UI |

## Monorepo vs standalone

| Context | Install | Activate |
| --- | --- | --- |
| **Development** | Clone repo to `wp-content/plugins/coverkit-usecases` | **CoverKit Use Cases** (root) |
| **Single use case** | Release zip → `wp-content/plugins/coverkit-usecase-<slug>/` | That use case plugin (+ CoverKit) |

WordPress only scans top-level `wp-content/plugins/` — nested `plugins/` folders are invisible to core. The root loader bridges that in dev; release zips are top-level plugins.

## Plugin headers

Every use case bootstrap must be a valid WordPress plugin file with at least:

- `Plugin Name`
- `Version`
- `Requires Plugins: coverkit`
- `Text Domain`

CI enforces this via `PluginHeaderTest`.

## Registration lifecycle

1. Bootstrap loads (via monorepo loader or standalone activation).
2. Bootstrap hooks `coverkit_init` (priority 5) and calls `coverkit_register_use_case( $slug, $args )`.
3. CoverKit registry boots registered types at priority 10.

Defer `require_once` of subclass files until the `coverkit_init` callback. `Requires Plugins: coverkit` ensures CoverKit is active before the hook runs.

## Label-only vs subclass

- **Label only** — omit `class` in registration args; CoverKit uses base `Use_Case` defaults.
- **Subclass** — extend `CoverKit\Use_Case`; override `recommended_settings()`, `use_case_mapping_sources()`, `use_case_settings_schema()`, and optionally `init()` for front-end behavior.

## Further reading

- [Create a use case](create-a-use-case.md)
- CoverKit [custom use case user guide](https://github.com/everpress/coverkit/blob/develop/docs/src/content/docs/user-guide/use-cases/custom-use-case.md)
- [Agent skills](agents.md)
