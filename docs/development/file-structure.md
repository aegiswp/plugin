# File Structure

Directory layout of the Aegis companion plugin repository.

## Top-Level Structure

```
aegis/
├── assets/                  # Admin CSS/JS, video editor sources, WooCommerce multi-step checkout CSS/JS
├── config/                  # Bootstrap PHP configs (blocks, patterns, integrations, …)
├── docs/                    # Plugin documentation
├── languages/               # aegis.pot (plugin text domain; not the theme catalog)
├── patterns/                # Block patterns (gated and always-on)
├── src/                     # PSR-4 PHP (namespace Aegis\Plugin\)
├── templates/               # Admin page templates
├── aegis.php                # Plugin bootstrap + theme gate + textdomain
├── package.json
└── readme.txt
```

## patterns/

| Directory | When registered |
|-----------|-----------------|
| `patterns/slider/` | Slider block implied on |
| `patterns/modal/` | Modal block enabled |
| `patterns/contact/` | Plugin + theme active |
| `patterns/author/` | Co-Authors Plus active **and** the integration on |
| `patterns/woocommerce/` | **WooCommerce active** |
| `patterns/wishlist/` | **WooCommerce + TI Wishlist active** |

There is no `patterns/blog/`. Related Posts layouts (`blog-related-posts-*`) live in the theme and unregister when **Aegis → Blocks → Related Posts** is implied off. Do not duplicate them here.

Commerce patterns use theme slug convention (`template-page-cart`, etc.) via `\Aegis\Utilities\Pattern::register_from_file()`.

## src/ Modules

| Module | Role |
|--------|------|
| `Admin/` | Menu, tabs, renderer, AJAX page loader |
| `Analytics/` | Tracker, Connectors panel |
| `Blocks/` | Map, Modal registration |
| `Connectors/` | Connectors admin page (BunnyCDN section; enqueues `assets/js/bunnycdn-settings.js`) |
| `Conditionals/` | Evaluator, post-content renderer, Smart Logic, Conditionals admin, IntegrationsPanel (plugin extras on Integrations) |
| `Hooks/` | Hook patterns admin |
| `Injection/` | Hook injection, preview (including Co-Authors Plus wrap of `core/post-author` and `co-authors/block`) |
| `CoAuthors/` | Multi-author `core/post-author*` replace, optional Author Schema extra |
| `Integrations/` | Third-party toggles, `WPFusion.php`, `ACF.php`, `MetaBox.php`, `CodeBlockPro.php`, `SyntaxHighlighting.php`, `AffiliateWP.php`, `EasyDigitalDownloads.php`, `FluentBooking.php`, `FluentForms.php`, `GravityForms.php`, `NinjaForms.php`, `LearnDash.php`, `LifterLMS.php`, `SenseiLMS.php`, `AllInOneSEO.php`, `RankMath.php`, `SEOPress.php`, `Yoast.php`, `BunnyCDN.php` (Connectors SaaS helper), `WooCommerce.php` (`is_plugin_active()` / `is_enabled()`), and `WooCommerce/MultiStepCheckout.php` |
| `Map/` / `Modal/` | Map/Modal blocks; Static Maps style encoding (`Map/Styles.php`); Modals instance dashboard |
| `Patterns/` | `CommercePatternRegistrar` |
| `Performance/` | Frontend optimizations, Performance admin page |
| `Seo/` | Schema delegation manager, video schema, and `Adapters/` (`AioseoAdapter`, `RankMathAdapter`, `YoastAdapter`, `SeopressAdapter`, `TsfAdapter`) |
| `Settings/` | Repository, export/import |
| `Snippets/` | Code snippets loader |
| `VisibilityPresets/` | Presets admin page |

## Config bootstrap order

Loaded from `aegis.php` after theme gate passes — see [[architecture]] for full list. Notable:

- `config/patterns.php` — always-on demo patterns
- `config/woocommerce.php` — `MultiStepCheckout` when `WooCommerce::is_enabled()`
- `config/woocommerce-patterns.php` — `CommercePatternRegistrar::init()`

## Next Steps

- [[architecture]] — Technical architecture
- [[building-assets]] — Build commands
- [[../../themes/aegis/docs/reference/file-structure|Theme File Structure]]
