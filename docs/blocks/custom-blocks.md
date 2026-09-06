# Custom Blocks (Plugin)

The Aegis plugin registers **Map** and **Modal** blocks only. Theme-owned blocks and `core/video` enhancements are handled elsewhere.

## Block Ownership

| Block | Registered by | Notes |
|-------|---------------|-------|
| `aegis/map` | **Plugin** | Google Maps + OpenStreetMap fallback |
| `aegis/modal` | **Plugin** | Popup/overlay dialogs |
| `aegis/countdown`, `slider`, `slide`, `toggle`, `toggle-content`, `related-posts` | **Theme** | Six theme blocks — see [[../../themes/aegis/docs/blocks/custom-blocks|Theme Custom Blocks]] |
| `core/video` | **WordPress core** | Enhanced by theme framework + plugin editor scripts (not a separate `aegis/video` block) |

The plugin does **not** register portable copies of theme blocks. Map and Modal require both the Aegis theme and this plugin.

## Feature Toggles

Enable or disable block features at **Aegis → Blocks** (`admin.php?page=aegis-blocks`). Features are **off by default** on new installs.

Parent block sections:

| Section | Block / variation |
|---------|-------------------|
| Accordion | List variation |
| Countdown | Countdown block (theme) |
| Counter | Paragraph variation |
| Icon | Core Icon block (`core/icon`). Aegis collections register on the WordPress Icon library (WP 7.1+). There is no Image Icon variation. |
| Image Lightbox | Core Image lightbox extras (grouped nav, zoom, thumbnails, swipe) — [Theme Image Lightbox](../../themes/aegis/docs/features/image-lightbox.md) |
| Image Compare | Pro `aegis/image-compare` before/after slider — extras at **Aegis → Blocks → Image Compare**; see [Pro Blocks](../../aegis-pro/docs/features/pro-blocks.md) |
| Map | Map block (plugin) |
| Marquee | Group variation — [[marquee]] |
| Modal | Modal block (plugin) |
| Newsletter | Search variation — [[newsletter]] |
| Query Loop | Core query enhancement — [[query-loop]] |
| Related Posts | Related Posts block (theme) — extras at **Aegis → Blocks → Related Posts**; see [Theme Related Posts](../../themes/aegis/docs/blocks/related-posts.md) |
| Slider | Slider block (theme) — extras at **Aegis → Blocks → Slider**; see [Theme Slider](../../themes/aegis/docs/blocks/slider.md) |
| SVG | Image variation (`is-style-svg` on `core/image`). Style registered in PHP. Extras at **Aegis → Blocks → SVG**. Paste markup in **SVG Settings**. Decorative icons use the Icon block, not this variation. |
| Toggle | Toggle block (theme) |
| Video | Core video enhancement |

Each section includes sub-feature toggles. Items marked **Pro** require Aegis Pro.

## Map Block

See [[map]] for full documentation: API keys, OSM fallback, schema, Static Maps facade styles, and **Aegis → Blocks → Map** extras.

- Interactive Google Maps with API keys under **Aegis → Connectors → Google Maps**
- Click-to-load Static Maps facade using the same style presets (and Pro custom JSON) as the interactive map
- Optional OpenStreetMap provider, plus an OSM fallback when Google has no key
- Schema.org Local Business output when the schema extra is enabled

## Modal Block

See [[modal]] for full documentation: layouts, triggers, **Aegis → Blocks → Modal** extras, and render fallbacks.

- Popup, off-canvas, bottom sheet, and fullscreen dialogs (the latter two extras fall back to popup when off)
- Click triggers (button, text, icon, image) plus Pro exit intent, scroll depth, and time delay
- Theme light/dark tokens on the dialog; close control **Top right of screen** (default) or **On dialog**
- Instance list and starters at **Aegis → Modals** (starters enable the extras their patterns use)

Manage modal overview stats at **Aegis → Modals**. Create modal content by adding `aegis/modal` blocks in the editor.

## Slider Block

Theme-owned (`aegis/slider` + `aegis/slide`). There is no parent Slider toggle: enabling any extra at **Aegis → Blocks → Slider** implies the block. Plugin demo patterns `aegis/slider-*` register only when the block is implied on.

Free extras: Slide, Fade, Navigation Arrows, Pagination Dots, Infinite Loop, Keyboard Navigation, Responsive Slides. Pro extras: Autoplay, Advanced Effects (fade), Thumbnails, Lightbox, Mousewheel, Aspect Ratio, Lazy Loading, Arrow Styles, Dot Styles.

Splide registers as WordPress handles (`splide`, `splide-autoscroll`) so the view script can mount. Default **Slides Per Page** is 1; existing saved blocks keep their stored value. Full attributes and Pro vs Splide notes: [Theme Slider](../../themes/aegis/docs/blocks/slider.md).

## Related Posts Block

Theme-owned (`aegis/related-posts`). There is no parent Related Posts toggle: enabling any extra at **Aegis → Blocks → Related Posts** implies the block.

| Extra | Inspector | Off fallback |
|-------|-----------|--------------|
| Related By | Related By | `auto` |
| Order By | Order By / Order | date, descending |
| Fallback Behavior | Fallback | latest posts |
| Style Variants | Grid / List / Cards / Minimal | `grid` |
| Excerpt Length | Excerpt Length | 20 words |
| Image Aspect Ratio | Image Aspect Ratio | `16/9` |

Heading, post count, columns, and display toggles stay available when the block is registered. Theme patterns `blog-related-posts-{grid,list,cards,minimal}` unregister when the block is implied off. Do not ship duplicate related-posts patterns from this plugin.

Full attributes, query filter, and Pro vs Query Loop notes: [Theme Related Posts](../../themes/aegis/docs/blocks/related-posts.md).

## Countdown Block

Theme-owned (`aegis/countdown`). There is no parent Countdown toggle: enabling any extra at **Aegis → Blocks → Countdown** implies the block.

Free extras: segment toggles, custom labels, separator, layout, expiry message, timezone, Schema.org Event. Pro extras: evergreen timer, animation styles, auto-restart, expiry actions, urgency styling.

The datetime picker stays available when the block is registered. Full attributes: [Theme Countdown](../../themes/aegis/docs/blocks/countdown.md).

## Image Compare Block

Pro-only (`aegis/image-compare`). There is no parent Image Compare toggle: enabling any extra at **Aegis → Blocks → Image Compare** implies the block in the inserter. Saved blocks still render when extras are off.

All extras are Pro: orientation, before/after labels (including show-on-hover), starting position, slide on hover, circle handle, smooth motion, fluid clip. Smooth Motion extra off leaves the library default (smooth, 100ms). Aspect ratio, height, object-fit, and object-position stay on the theme Image inspector (not Blocks extras); height applies CSS custom properties on the wrapper. Pick two images on the block; extras only expose those inspector controls.

See [Pro Blocks](../../aegis-pro/docs/features/pro-blocks.md).

## Toggle Block

Theme-owned (`aegis/toggle` + `aegis/toggle-content`). There is no parent Toggle toggle: enabling any extra at **Aegis → Blocks → Toggle** implies the block.

Free extras: pill, switch, and button switcher styles; alignment; custom labels. Pro extras: URL sync, state persistence, animations (fade, slide, flip, scale on click), nested toggles, conditional visibility.

Pill and button styles follow Aegis light/dark tokens (page surface and theme button colors). The block is a two-view content switcher, not an accordion. Heading tag, icon type, FAQ schema, and allow-multiple were accordion leftovers and are removed. Full attributes: [Theme Toggle](../../themes/aegis/docs/blocks/toggle.md).

## Next Steps

- [[block-variations]] — Framework variations and toggles
- [[map]] — Map block details
- [[modal]] — Modal block details
- [[marquee]] — Group Marquee variation
- [[newsletter]] — Search Newsletter variation
- [Theme Slider](../../themes/aegis/docs/blocks/slider.md) — Slider extras
- [Theme Related Posts](../../themes/aegis/docs/blocks/related-posts.md) — Related Posts extras
- [Theme Countdown](../../themes/aegis/docs/blocks/countdown.md) — Countdown extras
- [Theme Toggle](../../themes/aegis/docs/blocks/toggle.md) — Toggle extras
- [[modals]] — Modals admin tab
- [[integrations-dashboard]] — Google Maps API keys
- [Pro Blocks](../../aegis-pro/docs/features/pro-blocks.md) — Image Compare extras
- [[../../themes/aegis/docs/blocks/custom-blocks|Theme Custom Blocks]]
