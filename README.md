# CoverKit Use Cases

Monorepo for **custom CoverKit use cases**. Each use case lives in its own plugin under `plugins/`, loaded automatically by the root WordPress plugin.

Requires the main [CoverKit](https://coverkit.com) plugin to be installed and active.

## Structure

```
coverkit-usecases/
├── coverkit-usecases.php           # WordPress plugin — loads all use cases
├── plugins/
│   └── coverkit-usecase-starter/   # example / test use case
├── composer.json                   # PHP dev tools (PHPCS)
└── package.json                    # npm scripts (lint wrappers)
```

## Local development

1. Install PHP dev dependencies:

   ```bash
   composer install
   ```

2. Ensure this folder is in `wp-content/plugins/coverkit-usecases/` (clone or symlink the repo there).

3. Activate **CoverKit** and **CoverKit Use Cases** in **Plugins** — all use cases under `plugins/` load automatically.

4. Open a CoverKit template in the editor — custom use cases appear in the **Use cases** sidebar.

## Adding a new use case

1. Copy `plugins/coverkit-usecase-starter` to `plugins/coverkit-usecase-<slug>`.
2. Rename the bootstrap PHP file and replace starter-specific names.
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

4. Add a row to the **Plugins** table below. No symlink or re-activation needed — the root plugin picks up new folders on the next request.

Or use the Cursor command **`/new-usecase <slug>`** — it scaffolds the plugin from the starter template (see `.cursor/rules/new-usecase.mdc`).

See CoverKit docs: [Custom use cases](https://github.com/everpress/coverkit/blob/develop/docs/src/content/docs/user-guide/use-cases/custom-use-case.md).

## Scripts

| Command | Description |
| --- | --- |
| `composer run lint:php` | Run PHPCS on plugin PHP files |
| `composer run lint:php:fix` | Auto-fix PHPCS issues |
| `npm run lint:php` | Same as `composer run lint:php` |

## Use cases

| Folder | Use case slug | Purpose |
| --- | --- | --- |
| [coverkit-usecase-starter](plugins/coverkit-usecase-starter/) | `starter` | Minimal editor-only test use case |
