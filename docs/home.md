# Aegis Plugin Documentation

Welcome to the documentation for the **Aegis** companion plugin. The plugin requires the [Aegis theme](../../themes/aegis/docs/home.md) and adds Map and Modal blocks, a unified admin dashboard, integrations, conditionals, code snippets, hook references, and privacy-friendly analytics.

## About the Plugin

The Aegis plugin is a **companion** to the Aegis theme. It will not bootstrap without the Aegis theme active — it is not designed for other block themes.

**Block ownership:**

| Layer | Registers |
|-------|-----------|
| **Theme** | Six custom blocks: countdown, slider, slide, toggle, toggle-content, related-posts |
| **Plugin** | Map (`aegis/map`), Modal (`aegis/modal`), admin dashboard, `core/video` editor extensions, **WooCommerce/TI Wishlist patterns** (when dependencies active) |
| **Pro** | Tabs, tab, image-compare; extends `core/video` and `core/query` |

Video is the WordPress **`core/video`** block (enhanced by the theme framework and plugin editor scripts), not a separate `aegis/video` block. The plugin does **not** register portable copies of theme blocks on non-Aegis themes.

## Documentation Contents

### Getting Started

- [[installation]] — Install and activate the plugin
- [[requirements]] — System requirements

### Admin

- [[dashboard-overview]] — Aegis admin menu and tabs

### Blocks

- [[custom-blocks]] — Map and Modal blocks
- [[map]] — Map block
- [[modal]] — Modal block
- [[marquee]] — Group Marquee variation
- [[block-variations]] — Feature toggles for framework block variations
- [[modals]] — Modals admin tab overview

### Features

- [[integrations-dashboard]] — Third-party plugin toggles
- [[analytics]] — Analytics providers and privacy settings
- [[conditional-logic]] — Block and page visibility rules
- [[code-snippets]] — CSS, JS, HTML, and PHP snippets
- [[visibility-presets]] — Saved visibility rule sets (Pro)
- [[injection-preview]] — Frontend hook position preview
- [[hook-patterns]] — Hook reference UI (CPT management requires Pro)
- [[seo-schema-delegation]] — SEO plugin schema delegation
- [[svg-upload]] — Secure SVG media upload settings
- [[general-settings]] — Export, import, and reset
- [[performance]] — Site-wide frontend optimizations and Query Loop Pro gating
- [[woocommerce-checkout]] — Multi-step checkout (integration on + multi-step template)
- [[plugin-patterns]] — Plugin-shipped block patterns

### Development

- [[building-assets]] — Build commands and `languages/aegis.pot`
- [[file-structure]] — Directory layout
- [[architecture]] — Plugin architecture

### Help

- [[faq]] — Frequently asked questions

---

**Plugin Version:** 1.0.0  
**Requires WordPress:** 6.9 or later  
**Requires PHP:** 7.4 or later  
**Requires Theme:** Aegis theme (required)  
**License:** GPL-2.0-or-later  
**Theme docs:** [Aegis Theme Documentation](../../themes/aegis/docs/home.md)
