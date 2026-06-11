# CoverKit Use Cases

Same guidance as [`AGENTS.md`](AGENTS.md). Cursor users: skills live under `.cursor/skills/`.

## Quick links

- Scaffold: `.cursor/skills/new-usecase/SKILL.md` or `/new-usecase <slug>` in Cursor
- Architecture: `docs/architecture.md`
- Create a use case: `docs/create-a-use-case.md`

## Development

```bash
composer install
composer run lint:php
COVERKIT_PLUGIN_DIR=../coverkit composer run test:php
```

Activate **CoverKit** and **CoverKit Use Cases** in WordPress for monorepo dev.
