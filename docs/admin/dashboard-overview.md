# Dashboard Overview

The Aegis plugin adds a top-level **Aegis** menu in WordPress admin when the Aegis theme is active (capability: `manage_options`, position 59).

Base URL pattern: `/wp-admin/admin.php?page={slug}`

## Admin Tabs

| Tab | Menu label | Page slug |
|-----|------------|-----------|
| Dashboard | Dashboard | `aegis-dashboard` |
| Blocks | Blocks | `aegis-blocks` |
| Modals | Modals | `aegis-modals` |
| Hooks | Hooks | `aegis-hook-patterns` |
| Code Snippets | Code Snippets | `aegis-snippets` |
| Presets | Presets | `aegis-visibility-presets` |
| Conditionals | Conditionals | `aegis-settings` |
| Integrations | Integrations | `aegis-integrations` |
| Connectors | Connectors | `aegis-connectors` |
| Performance | Performance | `aegis-performance` |
| Settings | Settings | `aegis-general-settings` |
| License | License | `aegis-license` *(Pro only)* |

> **Note:** The Conditionals tab uses the label "Conditionals" but the page slug is `aegis-settings`.

## Dashboard Features

The Dashboard tab is a product home: a pulse of your setup, shortcuts into work, and a quiet footer.

- At-a-glance stats (blocks, conditionals, integrations, hook patterns, snippets)
- Shortcuts to Site Editor destinations plus Code Snippets, Hook Patterns, and Performance
- Getting Started (first visit; dismissible per user)
- Footer: system information and a short Pro CTA (full Pro feature list lives on **License**)

## In-admin navigation

Links between Aegis screens (WP admin submenu, dashboard cards, in-page links, GET filters, and clicking the current screen again) load through `admin-ajax.php` (`aegis_load_admin_page`) and swap `#wpbody-content` plus the top bar. After import/reset/purge, the current screen is refreshed the same way instead of a full wp-admin reload. Snippet **edit** screens, `admin-post.php` actions, and non-Aegis admin URLs still do a full load.

On plugin activation, a welcome redirect sends you to **Aegis → Dashboard** (`?aegis_welcome=1` transient).

## Export / Import Toolbar

Blocks, Conditionals, Integrations, Connectors, Performance, Snippets, and Settings pages include a toolbar with **Export**, **Import**, and **Reset** actions. See [[general-settings]] for export scope. Performance reset only clears Performance keys; Blocks reset leaves them unchanged. **Modals**, **Hooks**, and **Presets** have no export toolbar — they list content instances.

## Modals Tab

Lists `aegis/modal` blocks on the site and creates draft pages from starters. Feature toggles remain at **Aegis → Blocks → Modal**. See [[modals]].

## Hooks Tab

Lists `aegis_hook_pattern` posts (Pro) with enable/disable, edit, delete, click-to-copy hook names, and whether each pattern is **Always** or **Conditional**. Condition types are configured at **Aegis → Conditionals**. See [[../features/hook-patterns]].

## Presets Tab

Lists saved visibility rule sets (Pro) with **Add New**, search, edit, and delete. Condition types are configured at **Aegis → Conditionals**. See [[../features/visibility-presets]].

## Blocks Tab Sections

The Blocks tab groups feature toggles by block. Site-wide performance toggles are at **Aegis → Performance** — see [[../features/performance]].

| Section | Purpose |
|---------|---------|
| **Query Loop** | Query shape, layout, AJAX, and WooCommerce sub-features |
| **Slider** | Includes **Lazy Loading** (Splide, Pro) |

See [[../features/performance|Performance]] and [[../../aegis-pro/docs/features/query-performance|Query Performance (Pro)]].

## Next Steps

- [[custom-blocks]] — Blocks tab
- [[../features/performance|Performance]] — Site-wide and query gating
- [[integrations-dashboard]] — Integrations tab
- [[connectors]] — Connectors tab
- [[code-snippets]] — Snippets tab
- [[../../aegis-pro/docs/features/license|License (Pro)]]
