# CoverKit Use Cases

Monorepo for **custom CoverKit use cases**. Each use case lives in its own WordPress plugin under `plugins/`.

Requires the main [CoverKit](https://coverkit.com) plugin to be installed and active.

## Structure

```
coverkit-usecases/
├── plugins/
│   └── coverkit-usecase-starter/   # example / test use case
├── bin/
│   └── link-local.sh               # symlink plugins into wp-content/plugins
├── composer.json                   # PHP dev tools (PHPCS)
└── package.json                    # npm scripts (lint wrappers)
```

## Local development

1. Install PHP dev dependencies:

   ```bash
   composer install
   ```

2. Link plugins into WordPress (from this directory):

   ```bash
   ./bin/link-local.sh
   ```

   This creates symlinks in the parent `plugins/` folder so WordPress can load each use case plugin.

3. Activate **CoverKit** and the use case plugin(s) in **Plugins**.

4. Open a CoverKit template in the editor — the custom use case appears in the **Use cases** sidebar.

## Adding a new use case

1. Copy `plugins/coverkit-usecase-starter` to `plugins/coverkit-usecase-<slug>`.
2. Rename the main PHP file and plugin headers to match the folder name.
3. Subclass `CoverKit\Use_Case` and register on `coverkit_init`:

   ```php
   add_action(
       'coverkit_init',
       function () {
           coverkit_register_use_case(
               'my_slug',
               array(
                   'class' => My_Use_Case::class,
                   'label' => __( 'My use case', 'coverkit-usecase-my-slug' ),
               )
           );
       },
       5
   );
   ```

4. Run `./bin/link-local.sh` again to symlink the new plugin.

Or use the Cursor command **`/new-usecase <slug>`** — it scaffolds the plugin from the starter template (see `.cursor/rules/new-usecase.mdc`).

See CoverKit docs: [Custom use cases](https://github.com/everpress/coverkit/blob/develop/docs/src/content/docs/user-guide/use-cases/custom-use-case.md).

## Scripts

| Command | Description |
| --- | --- |
| `composer run lint:php` | Run PHPCS on `plugins/` |
| `composer run lint:php:fix` | Auto-fix PHPCS issues |
| `npm run lint:php` | Same as `composer run lint:php` |

## Plugins

| Plugin | Use case slug | Purpose |
| --- | --- | --- |
| [coverkit-usecase-starter](plugins/coverkit-usecase-starter/) | `starter` | Minimal editor-only test use case |
