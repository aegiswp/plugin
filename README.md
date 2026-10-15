# Aegis Plugin

Companion plugin for the [Aegis block theme](https://github.com/aegiswp/theme). Adds custom blocks (Map, Modal), the Aegis admin dashboard, integrations, conditionals, code snippets, a Hooks location catalog (pattern CPT requires Pro), and analytics.

**Canonical repository:** [github.com/aegiswp/plugin](https://github.com/aegiswp/plugin)

This directory is the local install path in WordPress Studio; the GitHub repo is the source of truth.

## Documentation

- [Plugin Wiki](https://github.com/aegiswp/plugin/wiki)
- [Plugin docs](docs/home.md) · [docs on GitHub](https://github.com/aegiswp/plugin/tree/main/docs)
- [Theme docs](../themes/aegis/docs/home.md)
- [Pro Wiki](https://github.com/aegiswp/pro/wiki) · [Pro docs](../aegis-pro/docs/home.md)
- [Plugin readme](readme.txt) — WordPress.org-style overview
- [Build commands](BUILD.md)
- [Translations](docs/development/building-assets.md#translations)

## Quick start

```bash
cd wp-content/plugins/aegis
npm ci
composer install
npm run build
composer lint
```

See [CONTRIBUTING.md](CONTRIBUTING.md) and [BUILD.md](BUILD.md). Release zips exclude GitHub/docs/Node/Composer tooling via `.distignore`.