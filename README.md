# Aegis Plugin

Welcome to the Aegis companion plugin development repository.

**Canonical repository:** [github.com/aegiswp/plugin](https://github.com/aegiswp/plugin)

This directory is the local install path in WordPress Studio; the GitHub repo is the source of truth.

## Status

[![CI](https://github.com/aegiswp/plugin/actions/workflows/ci.yml/badge.svg)](https://github.com/aegiswp/plugin/actions/workflows/ci.yml)
[![Spelling](https://github.com/aegiswp/plugin/actions/workflows/spelling.yml/badge.svg)](https://github.com/aegiswp/plugin/actions/workflows/spelling.yml)
[![Release](https://github.com/aegiswp/plugin/actions/workflows/release.yml/badge.svg)](https://github.com/aegiswp/plugin/actions/workflows/release.yml)

##### Table of Contents

- [Introduction](#introduction)
- [What it includes](#what-it-includes)
  - [Block ownership](#block-ownership)
  - [Custom blocks](#custom-blocks)
  - [Admin dashboard](#admin-dashboard)
  - [Integrations](#integrations)
  - [Code snippets](#code-snippets)
  - [Hooks](#hooks)
  - [Conditionals](#conditionals)
  - [Analytics & connectors](#analytics--connectors)
  - [Patterns](#patterns)
- [Aegis Pro](#aegis-pro)
- [Getting started](#getting-started)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Release version](#release-version)
- [Local development](#local-development)
- [Contributing](#contributing)
- [Resources](#resources)
- [Credits](#credits)

## Introduction

Aegis is the **companion plugin** for the [Aegis block theme](https://github.com/aegiswp/theme). It adds Map and Modal blocks, a unified admin dashboard, third-party integrations, display conditionals, code snippets, a Hooks location catalog, and privacy-friendly analytics.

The plugin **requires the Aegis theme**. It will not bootstrap on other themes — activate the theme before the plugin.

For WordPress.org-style packaging notes and external-service disclosures, see [readme.txt](readme.txt). Full feature docs live in [docs/](docs/home.md) and the [Plugin Wiki](https://github.com/aegiswp/plugin/wiki).

## What it includes

### Block ownership

| Layer | Registers |
|-------|-----------|
| **Theme** | Countdown, Slider, Slide, Toggle, Toggle Content, Related Posts |
| **Plugin** (this repo) | Map (`aegis/map`), Modal (`aegis/modal`), Modal starter patterns (`aegis/modal-*`), admin dashboard, `core/video` editor extensions, WooCommerce / TI Wishlist patterns when dependencies are active |
| **Pro** | Tabs, tab, image-compare; extends `core/video` and `core/query`; hook pattern CPT render |

Video uses WordPress **`core/video`** (theme framework + plugin editor scripts), not a separate `aegis/video` block.

### Custom blocks

Enable or disable parent blocks and per-block features from **Aegis → Blocks**:

| Block / feature | Notes |
|-----------------|-------|
| Map | `aegis/map` — Google Maps with OpenStreetMap fallback when no API key is set |
| Modal | `aegis/modal` — dialog block, Modals admin tab, `[aegis_modal id="…"]` shortcode |
| Accordion, Counter, Icon, Lightbox, Compare, Marquee, Newsletter, Query Loop, Related Posts, Slider, SVG, Toggle, Countdown | Feature toggles for theme / core enhancements |
| Video | Editor extensions for `core/video` |
| Schema | FAQ, Event, Local Business, and Video JSON-LD where supported |

Documentation: [Custom blocks](docs/blocks/custom-blocks.md) · [Map](docs/blocks/map.md) · [Modal](docs/blocks/modal.md) · [Modals tab](docs/blocks/modals.md)

### Admin dashboard

Open **Aegis** in the WordPress admin for:

| Screen | Purpose |
|--------|---------|
| **Dashboard** | Overview and entry points |
| **Blocks** | Per-block and variation feature toggles |
| **Integrations** | Third-party plugin compatibility (off by default) |
| **Conditionals** | Visibility extras available to blocks and Smart Logic |
| **Code Snippets** | CSS, JS, HTML, and PHP at hook locations |
| **Hooks** | Location catalog and instance list (pattern CPT create/render needs Pro) |
| **Connectors** | Google Maps, analytics providers, BunnyCDN credentials |
| **Performance** | Site-wide frontend optimizations |
| **Settings** | Export, import, reset, SVG upload, uninstall data |

See [Dashboard overview](docs/admin/dashboard-overview.md).

### Integrations

Manage compatibility from **Aegis → Integrations**. Toggles stay off until the target plugin is active. Admin notices remind you when a supported plugin is installed but its integration is disabled.

| Category | Plugins |
|----------|---------|
| **E-commerce** | WooCommerce, Easy Digital Downloads, AffiliateWP |
| **LMS** | LearnDash, LifterLMS, Sensei LMS |
| **Forms** | Gravity Forms, Ninja Forms, Fluent Forms, Fluent Booking |
| **Community** | bbPress (forum styling, Page template wrap, breadcrumbs, snippet locations) |
| **Content** | Co-Authors Plus (multi-author blocks, optional Author Schema) |
| **CRM** | FluentCRM, WP Fusion |
| **SEO** | Rank Math, Yoast SEO, All in One SEO, SEOPress (per-plugin schema delegation and breadcrumb styling) |
| **Developer** | Advanced Custom Fields, Meta Box, Code Block Pro, Syntax Highlighting Code Block |

When an SEO integration is on, FAQ / Event / Local Business / Video schema (and Pro video sitemaps) can be delegated to that plugin. Each SEO stack has its own description on the Integrations screen.

Full toggle behavior: [Integrations dashboard](docs/features/integrations-dashboard.md) · Theme CSS notes: [Theme plugin integrations](../themes/aegis/docs/features/plugin-integrations.md)

### Code snippets

**Aegis → Code Snippets** stores CSS, JavaScript, HTML, or PHP under `uploads/aegis-snippets/`. Snippets attach to theme and integration hook locations. PHP requires an explicit admin opt-in and auto-disables on fatal errors. Content snippets can also render via shortcode.

See [Code snippets](docs/features/code-snippets.md).

### Hooks

**Aegis → Hooks** lists injection locations and instances. Creating and rendering hook patterns (block CPT) requires **[Aegis Pro](https://github.com/aegiswp/pro)**. Without Pro, **Add New** links to **Aegis → License**. Free sites still use the location catalog for snippets.

See [Hook patterns](docs/features/hook-patterns.md).

### Conditionals

Show or hide blocks by user role, capability, device, date, URL, schedule, accessibility, and integration extras (including WP Fusion tag / list / CRM logged-in when enabled). Visibility presets are a Pro feature.

See [Conditional logic](docs/features/conditional-logic.md).

### Analytics & connectors

Optional, opt-in tags under **Aegis → Connectors** (one tab per service): Google Analytics 4, Google Tag Manager, Plausible, Fathom, Microsoft Clarity, Matomo, Meta Pixel. Google Maps browser and server keys live under **Connectors → Google Maps**. Nothing is sent until you save credentials and enable a provider.

See [Analytics](docs/features/analytics.md) · External-service disclosures in [readme.txt](readme.txt).

### Patterns

| Source | Patterns |
|--------|----------|
| This plugin | Modal starters (`aegis/modal-*`) when Modal is enabled; WooCommerce / TI Wishlist patterns when those plugins and integrations allow |
| Theme | Generic FSE patterns (~200+) |
| Pro | Pattern control extras (unregister third-party patterns when toggled) |

See [Plugin patterns](docs/features/plugin-patterns.md).

## Aegis Pro

The free plugin is complete for Map/Modal, integrations toggles, snippets, conditionals, Hooks catalog, analytics, and SEO delegation. **[Aegis Pro](https://github.com/aegiswp/pro)** adds hook pattern CPT rendering, BunnyCDN Stream video, advanced query conditions, visibility presets, pattern control, and Pro blocks (tabs, image-compare). Pro is not distributed on WordPress.org.

- [Pro Wiki](https://github.com/aegiswp/pro/wiki) · [Pro docs](../aegis-pro/docs/home.md)

## Getting started

### Requirements

| Requirement | Minimum |
|-------------|---------|
| **Aegis theme** | Required (activate first) |
| WordPress | 6.9+ (tested up to 7.1.3) |
| PHP | 7.4+ |
| Node.js / npm | 20+ / 9+ (build only; see `.nvmrc`) |
| Composer | 2+ (PHPCS / lint only; no production `vendor/` in release zips) |
| License | [GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html) |

### Installation

1. Install and activate the [Aegis theme](https://github.com/aegiswp/theme).
2. Upload this plugin to `/wp-content/plugins/aegis/` (or install a release zip).
3. Activate **Aegis** under **Plugins**.
4. Open **Aegis** in the admin — features are **off by default**; enable only what you need.

Step-by-step: [Installation docs](docs/getting-started/installation.md)

### Release version

Plugin version is defined in `aegis.php` (plugin header) and should stay aligned with `readme.txt` (`Stable tag`) and `CHANGELOG.md` when cutting a release.

Release zips are built by `.github/workflows/release.yml` with `.distignore` (and matching `.gitattributes` `export-ignore`). Runtime uses `src/autoload.php` — **no production Composer `vendor/`** in free-plugin zips.

See [.github/WORKFLOWS.md](.github/WORKFLOWS.md) · [BUILD.md](BUILD.md)

## Local development

```bash
cd wp-content/plugins/aegis
npm ci
composer install
npm run build
composer lint
```

| Command | Purpose |
|---------|---------|
| `npm run build` | Admin + video editor assets |
| `npm run build:admin` / `build:editor` | Individual asset builds |
| `npm run translate` / `translate:studio` | Regenerate `languages/aegis.pot` |
| `composer lint` | PHPCS (WordPress Coding Standards) |

Text domain: `aegis`. PHP namespace: `Aegis\Plugin\`.

Map and Modal block assets under `src/Blocks/map/` and `src/Blocks/modal/` are hand-maintained IIFEs — do not run `npm run build:blocks` unless you intend to replace those sources.

Details: [CONTRIBUTING.md](CONTRIBUTING.md) · [Building assets](docs/development/building-assets.md)

## Contributing

Thank you for contributing. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

- Branches: `main` (production), `dev` (integration), `feature/*` / `bugfix/*` / `hotfix/*`
- Open PRs against `dev` unless the change is a hotfix for `main`
- Keep CI green (build, PHPCS, required files)

Related repositories:

- Theme: [aegiswp/theme](https://github.com/aegiswp/theme)
- Pro: [aegiswp/pro](https://github.com/aegiswp/pro)

## Resources

| Resource | Link |
|----------|------|
| Plugin Wiki | https://github.com/aegiswp/plugin/wiki |
| Plugin docs | [docs/home.md](docs/home.md) |
| Theme docs | [../themes/aegis/docs/home.md](../themes/aegis/docs/home.md) |
| Pro Wiki / docs | https://github.com/aegiswp/pro/wiki · [../aegis-pro/docs/home.md](../aegis-pro/docs/home.md) |
| WordPress.org-style readme | [readme.txt](readme.txt) |
| Changelog | [CHANGELOG.md](CHANGELOG.md) |

## Credits

Aegis Plugin — Atmostfear Entertainment S.A.S.  
Distributed under the terms of the GNU GPL (GPL-2.0-or-later).
