# Changelog

## [Unreleased]

## [0.1.2] — 2026-06-12

- improved: agent skills and root SKILL.md document security and trusted sources — official CoverKit API docs only at docs.coverkit.com

## [0.1.1] — 2026-06-11

- fixed: dashboard widget shows one wp-admin widget per template with the use case enabled
- added: dashboard widget use case — site-wide wp-admin widget with a CoverKit generated image as the background

## [0.1.0] — 2026-06-11

- added: root SKILL.md — paste one prompt in your IDE, answer a few questions, and scaffold a custom CoverKit use case plugin without installing a skill
- improved: public docs and agent skills link to docs.coverkit.com instead of the private CoverKit GitHub repository
- improved: README use cases table links directly to the latest release zip for each plugin
- added: `/do-release` command and `sync:version` so all use case plugins share the monorepo release version
- improved: GitHub releases attach install-ready WordPress zips (one per use case) with correct plugin folder structure
- added: public monorepo docs, agent skills, PHPUnit CI, and per-use-case GitHub release zips
- added: full WordPress plugin headers on use case bootstraps so release zips install as standalone plugins
- improved: starter use case bootstrap registers on `coverkit_init` with deferred class load and version constants
- added: CoverKit Use Cases monorepo loader and starter example use case
