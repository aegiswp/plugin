# Contributing to the Aegis Plugin

Thank you for your interest in contributing to the Aegis free WordPress plugin ([github.com/aegiswp/plugin](https://github.com/aegiswp/plugin)).

## Development Setup

1. Clone the repository into `wp-content/plugins/aegis` (or symlink it there):

```bash
git clone https://github.com/aegiswp/plugin.git
cd plugin
```

2. Install Node.js dependencies (Node 20 — see `.nvmrc`):

```bash
npm ci
```

3. Install PHP dev dependencies (PHPCS / WPCS):

```bash
composer install
```

4. Build assets:

```bash
npm run build
```

5. Activate the **Aegis** theme and this plugin in a local WordPress site (WordPress Studio, wp-env, or similar).

## Coding Standards

```bash
composer lint          # PHPCS (WordPress Coding Standards)
```

Text domain: `aegis`. PHP namespace: `Aegis\Plugin\`.

## Branch Strategy

- `main` — production-ready code
- `dev` — integration branch
- `feature/*` / `bugfix/*` / `hotfix/*` — work branches

Open pull requests against `dev` unless the change is a hotfix for `main`.

## Pull Requests

Use the PR template. Include testing notes (WordPress, PHP, browser) and confirm CI is green.

## Related Repositories

- Theme: [aegiswp/theme](https://github.com/aegiswp/theme)
- Pro: [aegiswp/pro](https://github.com/aegiswp/pro)

## License

Contributions are licensed under GPL-2.0-or-later.
