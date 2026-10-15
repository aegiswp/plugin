# Aegis Plugin

Welcome to the Aegis companion plugin development repository.

![Aegis](https://github.com/aegiswp/plugin/blob/main/_wordpress-org/screenshot-1.png)

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
    - [E-commerce](#e-commerce)
    - [LMS](#lms)
    - [Forms](#forms)
    - [Community](#community)
    - [Content](#content)
    - [CRM](#crm)
    - [SEO](#seo)
    - [Developer](#developer)
    - [Connectors (related)](#connectors-related)
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

The Aegis stack splits registration across three layers. This plugin does **not** re-register theme blocks for use on other themes.

#### Theme (`aegiswp/theme`)

| Block | Name | Notes |
|-------|------|-------|
| Countdown | `aegis/countdown` | Theme-owned; extras gated at **Aegis → Blocks → Countdown** |
| Slider | `aegis/slider` | Theme-owned; pair with `aegis/slide` |
| Slide | `aegis/slide` | Child of Slider |
| Toggle | `aegis/toggle` | Theme-owned disclosure |
| Toggle Content | `aegis/toggle-content` | Toggle panel content |
| Related Posts | `aegis/related-posts` | Theme-owned; extras at **Aegis → Blocks → Related Posts** |

Theme docs: [Custom blocks](../themes/aegis/docs/blocks/custom-blocks.md)

#### Plugin (this repository)

| Capability | Name / location | Notes |
|------------|-----------------|-------|
| Map block | `aegis/map` | Google Maps + OpenStreetMap fallback |
| Modal block | `aegis/modal` | Popup / off-canvas / sheet dialogs |
| Modal starters | `aegis/modal-*` | Register when Modal is enabled |
| Modals admin | **Aegis → Modals** | Instance list, shortcode copy, starter drafts |
| Modal shortcode | `[aegis_modal id="…"]` | Render a Modal from its host (including drafts) |
| Admin dashboard | **Aegis** menu | Blocks, Integrations, Snippets, Hooks, Connectors, … |
| Video editor | `core/video` | Editor extensions (not a separate `aegis/video` block) |
| Commerce patterns | WooCommerce / TI Wishlist | Gated when those plugins and toggles allow |

#### Pro (`aegiswp/pro`)

| Capability | Name / notes |
|------------|--------------|
| Tabs | `aegis/tabs`, `aegis/tab` |
| Image Compare | `aegis/image-compare` (Compare extras on **Blocks**) |
| Video / query | Extends `core/video` (BunnyCDN Stream) and `core/query` conditions |
| Hook patterns | CPT create + frontend render (`aegis_hook_pattern`) |
| Visibility presets | Saved rule sets under **Aegis → License** / Presets when Pro is active |

#### Core (WordPress)

| Block | Role |
|-------|------|
| `core/video` | Enhanced by theme framework + this plugin’s editor scripts |
| `core/query` | Query Loop extras from this plugin; advanced conditions in Pro |
| `core/icon`, `core/image`, `core/list`, `core/group`, … | Variations and extras toggled under **Aegis → Blocks** |

### Custom blocks

Parent sections and sub-feature toggles live at **Aegis → Blocks** (`admin.php?page=aegis-blocks`). Features are **off by default** on new installs. Items marked **Pro** need Aegis Pro.

#### Registered by this plugin

| Block | Name | What you get |
|-------|------|--------------|
| **Map** | `aegis/map` | Interactive Google Maps; Static Maps facade; optional OSM provider; OSM fallback when Google has no key; Local Business schema when that extra is on. Keys: **Aegis → Connectors → Google Maps**. Docs: [Map](docs/blocks/map.md) |
| **Modal** | `aegis/modal` | Popup, off-canvas, bottom sheet, fullscreen (sheet/fullscreen fall back to popup when those extras are off). Click triggers; Pro exit intent / scroll / time delay. Instance list at **Aegis → Modals**; shortcode `[aegis_modal id="…"]`. Docs: [Modal](docs/blocks/modal.md) · [Modals tab](docs/blocks/modals.md) |

#### Theme blocks (extras gated here)

| Block | Name | Extras (summary) |
|-------|------|------------------|
| **Countdown** | `aegis/countdown` | Theme block; enable extras under **Blocks → Countdown** |
| **Slider** | `aegis/slider` + `aegis/slide` | Free: slide/fade, arrows, dots, loop, keyboard, responsive. Pro: autoplay, thumbnails, lightbox, mousewheel, aspect ratio, lazy load, arrow/dot styles. Docs: [Theme Slider](../themes/aegis/docs/blocks/slider.md) |
| **Toggle** | `aegis/toggle` + `aegis/toggle-content` | Theme disclosure; extras under **Blocks → Toggle** |
| **Related Posts** | `aegis/related-posts` | Related By, Order By, Fallback, style variants. Docs: [Theme Related Posts](../themes/aegis/docs/blocks/related-posts.md) |

#### Core variations and enhancements (toggles here)

| Section | Base | Description |
|---------|------|-------------|
| **Accordion** | `core/list` | List → interactive accordion |
| **Counter** | `core/paragraph` | Animated number counter |
| **Icon** | `core/icon` | Aegis collections on the WordPress Icon library (WP 7.1+). No Image Icon variation |
| **Lightbox** | `core/image` | Grouped nav, zoom, thumbnails, swipe (theme lightbox extras) |
| **Compare** | Pro `aegis/image-compare` | Before/after slider; extras at **Blocks → Compare** |
| **Marquee** | `core/group` | Continuous horizontal ticker (Group variation). Docs: [Marquee](docs/blocks/marquee.md) |
| **Newsletter** | `core/group` / search variation | Newsletter signup layout. Docs: [Newsletter](docs/blocks/newsletter.md) |
| **Query Loop** | `core/query` | Query shape, layout, AJAX, field pickers, WooCommerce sub-features. Docs: [Query Loop](docs/blocks/query-loop.md) |
| **SVG** | `core/image` (`is-style-svg`) | Inline SVG with sanitization; paste markup in **SVG Settings**. Decorative icons use Icon, not this variation |
| **Video** | `core/video` | Editor extensions; Stream / advanced hosting in Pro |

#### Schema extras

Where supported on Map / video / related blocks, optional JSON-LD for **FAQ**, **Event**, **Local Business**, and **Video**. SEO plugin integrations can **delegate** those outputs when enabled (see [SEO](#seo)).

Full Blocks tab reference: [Custom blocks](docs/blocks/custom-blocks.md) · [Block variations](docs/blocks/block-variations.md)

### Admin dashboard

Top-level **Aegis** menu (capability `manage_options`, position 59) when the Aegis theme is active. Base URL: `/wp-admin/admin.php?page={slug}`.

In-admin links between Aegis screens load via AJAX (`aegis_load_admin_page`) and swap `#wpbody-content` without a full reload. Snippet edit screens and non-Aegis URLs still do a full load. On activation, a welcome redirect opens **Dashboard**.

| Screen | Menu label | Page slug | Purpose |
|--------|------------|-----------|---------|
| **Dashboard** | Dashboard | `aegis-dashboard` | Product home: at-a-glance stats (blocks, conditionals, integrations, hook patterns, snippets), Site Editor shortcuts, Getting Started (dismissible), system footer + Pro CTA |
| **Blocks** | Blocks | `aegis-blocks` | Per-block / variation feature toggles (off by default). Export / Import / Reset toolbar |
| **Modals** | Modals | `aegis-modals` | Lists `aegis/modal` instances; starter drafts; shortcode copy. Feature toggles stay on **Blocks → Modal**. No export toolbar |
| **Hooks** | Hooks | `aegis-hook-patterns` | Location catalog + instance table. Pattern CPT create/render needs Pro (**Add New** → License). No export toolbar |
| **Code Snippets** | Code Snippets | `aegis-snippets` | CSS / JS / HTML / PHP at hook locations; location catalog under Settings tab. Export / Import / Reset |
| **Presets** | Presets | `aegis-visibility-presets` | Saved visibility rule sets (**Pro**). Free UI may redirect toward License. No export toolbar |
| **Conditionals** | Conditionals | `aegis-settings` | Which condition types are available to blocks, Smart Logic, and hook patterns. Export / Import / Reset |
| **Integrations** | Integrations | `aegis-integrations` | Third-party toggles by category (off by default). Export / Import / Reset |
| **Connectors** | Connectors | `aegis-connectors` | Google Maps keys, analytics providers, BunnyCDN credentials. Reset clears Connectors-owned data only |
| **Performance** | Performance | `aegis-performance` | Site-wide frontend optimizations and Query Loop Pro gating. Export / Import / Reset |
| **Settings** | Settings | `aegis-general-settings` | Global export / import / reset, SVG upload, delete-data-on-uninstall |
| **License** | License | `aegis-license` | Get Pro upsell when Pro is inactive; license activation when Pro is active |

See [Dashboard overview](docs/admin/dashboard-overview.md) · [General settings](docs/features/general-settings.md)

### Integrations

Manage compatibility from **Aegis → Integrations** (`admin.php?page=aegis-integrations`).

**Behavior (all stacks):**

- Integrations are **off by default** on new installs.
- A toggle can only turn on when that plugin is **installed and active**.
- Admin notices remind you when a supported plugin is present but its integration is disabled.
- Stack headers and rows use brand marks when available; otherwise dashicons.
- Categories and plugins in each category are listed alphabetically in the UI.
- Full toggle / CSS / location details: [Integrations dashboard](docs/features/integrations-dashboard.md) · Theme CSS: [Plugin integrations](../themes/aegis/docs/features/plugin-integrations.md)

#### E-commerce

| Plugin | What enabling does |
|--------|--------------------|
| **WooCommerce** | Framework shop / cart / checkout CSS; breadcrumb CSS and `.aegis-breadcrumb-separator` wrappers; unregisters WooCommerce plugin patterns unless **WooCommerce Patterns** is on; snippet locations (`aegis_before/after_woocommerce_checkout`, cart pair); multi-step checkout assets on the multi-step template. Pro: cart, customer, and product conditions |
| **Easy Digital Downloads** | Framework download / checkout / cart CSS when EDD markup is present; snippet locations (`aegis_before/after_edd_download`). Pro: cart, customer, and download conditions |
| **AffiliateWP** | Framework Affiliate Area + login/register CSS; skips bundled `affwp-forms` on the frontend; snippet locations (`aegis_before/after_affwp_dashboard`). Pro: referral visit, affiliate account, earnings |

#### LMS

| Plugin | What enabling does |
|--------|--------------------|
| **LearnDash** | Framework course / lesson / topic / quiz / profile CSS; Focus Mode logo from theme custom logo; `aegis-learndash-page` body class; snippet locations (course, lesson, topic, quiz, focus header/footer). Pro: pattern control, video progression |
| **LifterLMS** | Framework course / lesson / syllabus CSS (tokens + dark mode); theme support; suppresses default sidebars; `aegis-lifterlms-page` body class; snippet locations (course, lesson). Pro: enrollment, completion, progress %, membership extras; pattern control; video progression |
| **Sensei LMS** | Framework course / lesson / quiz / progress CSS; theme support; disables default Sensei styles; `aegis-sensei-page` body class; snippet locations (course, lesson, quiz). Pro: enrollment / completion extras; pattern control |

#### Forms

| Plugin | What enabling does |
|--------|--------------------|
| **Fluent Forms** | Framework form CSS; deactivates default public form styles; snippet locations (`aegis_before/after_fluentform`). Pro: submitted / count / field conditions; **Fluent Forms Patterns** control |
| **Fluent Booking** | Framework calendar / booking CSS when Fluent Booking markup is present. Pro: booking status / upcoming / past extras; **Fluent Booking Patterns** control |
| **Gravity Forms** | Framework form CSS; disables GF theme CSS; snippet locations (`aegis_before/after_gform` via `gform_get_form_filter`) |
| **Ninja Forms** | Framework form CSS; dequeues opinionated NF display styles; snippet locations (`aegis_before/after_nf_form`) |

#### Community

| Plugin | What enabling does |
|--------|--------------------|
| **bbPress** | Framework forum CSS (`plugins/bbpress.css`: avatars, typography, forms, topic rows, posts, My Account–style member layout); dequeues `bbp-default` / `bbp-default-rtl`; wraps forum views in the theme **Page** template (`locate_block_template` → `page.html`); breadcrumb styling; snippet locations (`aegis_before/after_bbpress_forum`, topic pair). No block-condition extras |

#### Content

| Plugin | What enabling does |
|--------|--------------------|
| **Co-Authors Plus** | Multi-author block rendering; optional **Author Schema** extra (JSON-LD Person, off by default — leave off if Yoast/Rank Math already output author schema); snippet locations around post-author / Co-Authors markup. Pro: **Co-Authors Plus Patterns** control |

#### CRM

| Plugin | What enabling does |
|--------|--------------------|
| **FluentCRM** | Email marketing CRM bridge. Pro: video funnels, tags, and lists |
| **WP Fusion** | **CRM Tags** and **CRM Lists** extras (separate toggles). Either extra also enables **Is logged in (CRM)** / Smart Logic **WP Fusion Logged-in (CRM)** (WP Fusion auto-login, not WordPress User Logged-in). Tag/list pickers use the CRM catalog |

#### SEO

Each SEO plugin has its **own** stack description on the Integrations screen. Enabling one can delegate FAQ, Event, Local Business, and Video schema (and Pro video sitemaps) away from built-in block markup.

| Plugin | What enabling does |
|--------|--------------------|
| **Rank Math** | Delegates FAQ / Event / Local Business / Video schema to Rank Math; styles Rank Math breadcrumbs; Pro video sitemaps |
| **Yoast SEO** | Delegates the same schema set to Yoast; styles Yoast breadcrumbs (`#breadcrumbs` / `.yoast-breadcrumbs`); Pro Video SEO sitemaps |
| **All in One SEO** | Delegates the same schema set to AIOSEO; Pro video sitemaps |
| **SEOPress** | Delegates the same schema set to SEOPress; styles SEOPress breadcrumbs; Pro video sitemaps |

See also [SEO schema delegation](docs/features/seo-schema-delegation.md).

#### Developer

| Plugin | What enabling does |
|--------|--------------------|
| **Advanced Custom Fields** | Field visibility (Pro inspector + hook-pattern conditions); Query Loop field pickers (needs **Blocks → Query Loop → Field Picker**); featured-image **ACF Image Field** also requires this toggle. Covers Secure Custom Fields on WordPress.org. No framework CSS. Pro video Dynamic Source offers ACF when on |
| **Meta Box** | Same field extras as ACF for Meta Box / Meta Box AIO; framework form CSS (`plugins/meta-box.css`); theme palette on Meta Box colour pickers; `aegis/metabox` bindings. Pro video Dynamic Source when on |
| **Code Block Pro** | Theme radius / gap / monospace overlay (`plugins/code-block-pro.css`) when Kevin Batdorf’s block markup is present. No extras |
| **Syntax Highlighting Code Block** | Theme code-color / radius / padding / line-number overlay (`plugins/syntax-highlighting-code-block.css`) when `hljs` / `shcb-` markup is present. No extras |

#### Connectors (related)

BunnyCDN and Google Maps are configured under **Aegis → Connectors** (not the Integrations category list above). BunnyCDN Stream API calls require Pro; credentials can be stored from the free plugin’s Connectors screen.

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
