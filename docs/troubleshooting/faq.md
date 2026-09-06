# Frequently Asked Questions

Common questions about the Aegis companion plugin.

## General

### Do I need the Aegis theme?

**Yes.** The Aegis plugin requires the Aegis theme and will not load without it. This is a hard gate — the plugin is not compatible with other block themes.

### Do I need Aegis Pro?

No. The free plugin includes Map and Modal blocks, integration toggles, code snippets, conditionals, hooks reference, analytics, and SEO schema delegation. Pro adds advanced video, query, hook pattern CPT, and Pro block features.

### What is the difference between theme, plugin, and Pro?

| Product | Includes |
|---------|----------|
| **Aegis theme** | FSE templates, **~200 generic theme patterns**, style variations, design system, six custom blocks (countdown, slider, slide, toggle, toggle-content, related-posts), `core/video` framework enhancements, WooCommerce **FSE templates** |
| **Aegis Plugin** | Map, Modal, admin dashboard, block toggles, snippets, conditionals, integrations, analytics, `core/video` editor extensions, **gated WooCommerce/TI Wishlist block patterns** |
| **Aegis Pro** | Hook pattern CPT, video/BunnyCDN on `core/video`, query conditions on `core/query`, tabs/tab/image-compare blocks, block extensions, license |

## Configuration

### Where do I enter Google Maps credentials?

**Aegis → Connectors → Google Maps** — browser and server API keys.

### Where do I configure analytics?

**Aegis → Connectors** — one tab per provider (Google Analytics, Tag Manager, Clarity, Plausible, Fathom, Matomo, Meta Pixel). Shared consent settings are on **Privacy**.

### Where are SVG upload settings?

**Aegis → Settings** — not under Blocks (Blocks has a separate SVG Image variation toggle). Decorative icons use **Aegis → Blocks → Icon** on the WordPress Icon block (`core/icon`), not an Image Icon variation.

### Where is WP Fusion configured?

Enable the plugin and its tag/list conditions at **Aegis → Integrations → WP Fusion**.

### How do I translate the plugin?

The plugin catalog is `languages/aegis.pot` (text domain `aegis`). Generate it from the plugin directory with `npm run translate` or `npm run translate:studio`. Do not add plugin strings to the theme POT (`wp-content/themes/aegis/languages/aegis.pot`). Pro uses `aegis-pro.pot`. See [[../development/building-assets#translations]].

## Blocks

### Which blocks require the plugin?

**Map** (`aegis/map`) and **Modal** (`aegis/modal`) are registered by the plugin. The theme registers six custom blocks. Video uses **`core/video`** (enhanced by the theme and plugin editor scripts).

### Can I use the plugin without the theme?

No. The plugin checks for the Aegis theme on load and displays an admin notice if it is missing.

### Can I disable block features?

Yes. Use **Aegis → Blocks** to toggle parent blocks and sub-features.

### Why don't I see WooCommerce patterns in the inserter?

WooCommerce block patterns register from `patterns/woocommerce/` only when **WooCommerce is active** and this plugin is loaded. Install WooCommerce and activate the Aegis plugin. FSE templates stay in the theme; patterns are plugin-owned. See [[../features/plugin-patterns]] and [[../../themes/aegis/docs/troubleshooting/faq|Theme FAQ]].

## Performance

### Where are performance settings?

**Aegis → Performance** — oEmbed, dashicons, heartbeat, embed facades, Query Loop Pro gate, and emoji scripts (Pro).

Slider lazy-load stays at **Aegis → Blocks → Slider**. BunnyCDN is at **Aegis → Connectors → BunnyCDN**.

See [[../features/performance|Performance]] for details. Pro per-block Query Loop options are documented in [[../../aegis-pro/docs/features/query-performance|Query Performance (Pro)]].

### Are performance toggles included in export?

Yes. They are stored in the `blocks` group of the global export bundle — see [[../features/general-settings|General Settings]].

## Data and Privacy

### Is data sent to third parties by default?

No. Analytics, Maps, and external services activate only when you configure credentials and enable them.

### Where are snippets stored?

In `uploads/aegis-snippets/`, outside the global settings export bundle.

## Next Steps

- [[installation]] — Install the plugin
- [[dashboard-overview]] — Admin tour
- [[../features/performance|Performance]] — Site-wide and query toggles
- [[../../themes/aegis/docs/troubleshooting/faq|Theme FAQ]]
