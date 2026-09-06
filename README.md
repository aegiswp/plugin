# Aegis Plugin

Companion plugin for the [Aegis block theme](https://github.com/aegiswp/theme). Adds custom blocks (Map, Modal), the Aegis admin dashboard, integrations, conditionals, hooks, and analytics.

**Canonical repository:** [github.com/aegiswp/plugin](https://github.com/aegiswp/plugin)

This directory is the local install path in WordPress Studio; the GitHub repo is the source of truth.

## Documentation

- [Plugin readme](readme.txt) — WordPress.org-style overview
- [Plugin docs](docs/home.md)
- [Theme docs](../themes/aegis/docs/home.md)
- [Build commands](BUILD.md)
- [Translations](docs/development/building-assets.md#translations)

## Quick start

```bash
cd wp-content/plugins/aegis
npm install
npm run build
```

PHP coding standards run from the theme directory (`composer run standards:check` in `wp-content/themes/aegis`).
