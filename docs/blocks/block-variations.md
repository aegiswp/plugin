# Block Variations (Plugin Toggles)

Block variations (Accordion List, Counter, Marquee, Newsletter, etc.) are implemented by the **Aegis framework** (`vendor/aegis/framework`) and gated by plugin feature toggles at **Aegis → Blocks**.

## How Toggles Work

1. The framework registers variations when a parent feature key is enabled in plugin settings.
2. `ServiceProvider::is_block_enabled()` reads `aegis_blocks` options.
3. When the plugin is inactive, framework defaults treat features as enabled.
4. Accordion (`core/list`), Newsletter (`core/search`), and SVG (`core/image`) styles are registered once in PHP (`register_style()`), not also via `BlockStyles`. The SVG inspector is `svg-editor.js` (no Optimize SVG / SVGOMG control).

## Available Variations

| Variation | Base Block | Toggle key |
|-----------|-----------|------------|
| Accordion List | `core/list` | `accordion` |
| Counter | `core/paragraph` | `counter` |
| Curved Text | `core/paragraph` | (framework) |
| Grid | `core/group` | (framework) |
| Marquee | `core/group` | `marquee` |
| Newsletter | `core/search` | `newsletter` |
| SVG | `core/image` | `svg` (`is-style-svg`). Extras: paste markup, mask, onclick, inline-in-text, inline `.svg` files. Not the Icon block — see [Theme SVG](../../themes/aegis/docs/blocks/block-variations.md#svg). |

See [[newsletter]] for Email Validation, Success Message, and Custom Placeholder extras.

> **Related Posts vs Query Loop:** The theme's **`aegis/related-posts`** block is a separate custom block (**Aegis → Blocks → Related Posts**). Query Loop extras apply only to **`core/query`**. Pro can add a related-posts *query mode* on Query Loop (`aegisProRelatedPosts`); that is not a block variation and is not the Related Posts block.

See [[query-loop]] for the full Query Loop extra list.

See [[../../themes/aegis/docs/blocks/block-variations|Theme Block Variations]] for usage and editor behavior.

## Sub-Features

Each parent block has many sub-feature toggles (~150 total). Examples:

- **Icon (`core/icon`):** Core Icon library collections, custom SVG, gallery, gradients — [Theme SVG Icons](../../themes/aegis/docs/features/svg-icons.md)
- **Image Lightbox (`core/image`):** grouped navigation, zoom, thumbnails, swipe — [Theme Image Lightbox](../../themes/aegis/docs/features/image-lightbox.md)
- **Query Loop (`core/query`):** post types, taxonomy, include/exclude, meta, layout, no results — [[query-loop]]. Query Loop **Performance** gating is at [[../features/performance|Aegis → Performance]]
- **Related Posts (`aegis/related-posts`):** Related By, Order By, Fallback, Style Variants, Excerpt Length, Image Aspect Ratio — [Theme Related Posts](../../themes/aegis/docs/blocks/related-posts.md). Not Query Loop’s `aegisProRelatedPosts` mode.
- **Slider (`aegis/slider`):** Fade, Navigation, Pagination, Loop, Keyboard, Responsive, Autoplay — [Theme Slider](../../themes/aegis/docs/blocks/slider.md). Default **Slides Per Page** is 1 (saved blocks keep their stored value). Pro: thumbnails, lightbox, mousewheel, aspect ratio, lazy load, arrow/dot styles.
- **Map (`aegis/map`):** markers, style presets (including the Static Maps facade), controls, OSM fallback, schema; Pro: clustering, directions, store locator, geolocation, heatmap, drawing, KML/GeoJSON, dynamic markers, custom icons, custom styles
- **Marquee (`core/group`):** pause on hover, direction, loop duration, repeat clones; Pro: responsive desktop duration from 782px — [[marquee]]
- **Newsletter (`core/search`):** email validation, success message, custom placeholder; signup vs decorative fields — [[newsletter]]
- **SVG (`core/image`):** Paste Markup, Mask Mode, Onclick, Inline SVG in text, Inline SVG files — [Theme SVG](../../themes/aegis/docs/blocks/block-variations.md#svg). Empty SVG shows the Aegis placeholder (preview-only) until markup is pasted in **SVG Settings**. No Optimize SVG control. Not the Icon block.
- **Video (`core/video`):** Custom Player (theme), Theater Mode and Keyboard Shortcuts (require Custom Player), Schema Markup; Pro: ambient light, thumbnail preview, touch gestures, playlists, sticky player, focus mode, save progress, chapters, email capture (includes CTA overlays / action bar / YouTube subscribe in the same module), analytics, multi-audio, privacy, video sitemap (SEO plugin adapters). There is no parent Video toggle — each extra is independent. Muted autoplay is per-block when any Engagement extra is on. BunnyCDN is **Aegis → Integrations / Connectors**, not a Blocks extra.
- **Countdown (`aegis/countdown`):** segments, labels, separator, layout, expiry message, timezone, Schema.org Event; Pro: evergreen, animations, auto-restart, expiry actions, urgency — [Theme Countdown](../../themes/aegis/docs/blocks/countdown.md)
- **Toggle (`aegis/toggle`):** pill, switch, and button switcher styles (light/dark via body and button tokens); alignment; custom labels; Pro: URL sync, persist, click animations (fade/slide/flip/scale), nested switchers, conditional visibility — [Theme Toggle](../../themes/aegis/docs/blocks/toggle.md). Not an accordion.

Pro-gated toggles display a Pro badge in the Blocks admin UI.

## Next Steps

- [[custom-blocks]] — Map and Modal blocks
- [[marquee]] — Group Marquee variation
- [[newsletter]] — Search Newsletter variation
- [[query-loop]] — Query Loop extras
- [Theme Slider](../../themes/aegis/docs/blocks/slider.md) — `aegis/slider` extras
- [Theme Related Posts](../../themes/aegis/docs/blocks/related-posts.md) — `aegis/related-posts` extras
- [Theme Countdown](../../themes/aegis/docs/blocks/countdown.md) — `aegis/countdown` extras
- [Theme Toggle](../../themes/aegis/docs/blocks/toggle.md) — `aegis/toggle` extras
- [Theme SVG](../../themes/aegis/docs/blocks/block-variations.md#svg) — `core/image` SVG variation extras
- [[../../themes/aegis/docs/blocks/block-variations|Theme Block Variations]] — Usage guide
- [[../../aegis-pro/docs/features/block-extensions|Pro Block Extensions]]
