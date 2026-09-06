# File Structure

Directory layout of the Aegis companion plugin repository.

## Top-Level Structure

```
aegis/
├── assets/                  # Admin CSS/JS and video editor sources
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
| `patterns/author/` | Plugin + theme active |
| `patterns/woocommerce/` | **WooCommerce active** |
| `patterns/wishlist/` | **WooCommerce + TI Wishlist active** |

There is no `patterns/blog/`. Related Posts layouts (`blog-related-posts-*`) live in the theme and unregister when **Aegis → Blocks → Related Posts** is implied off. Do not duplicate them here.

Commerce patterns use theme slug convention (`template-page-cart`, etc.) via `\Aegis\Utilities\Pattern::register_from_file()`.

## src/ Modules

| Module | Role |
|--------|------|
| `Admin/` | Menu, tabs, renderer, AJAX page loader |
| `Analytics/` | Tracker, integrations panel |
| `Blocks/` | Map, Modal registration |
| `Conditionals/` | Visibility rules |
| `Hooks/` | Hook patterns admin |
| `Injection/` | Hook injection, preview |
| `Integrations/` | Third-party toggles |
| `Map/` / `Modal/` | Map/Modal blocks; Static Maps style encoding (`Map/Styles.php`); Modals instance dashboard |
| `Patterns/` | `CommercePatternRegistrar` |
| `Performance/` | Frontend optimizations, Performance admin page |
| `Settings/` | Repository, export/import |
| `Snippets/` | Code snippets loader |
| `VisibilityPresets/` | Presets admin page |

## Config bootstrap order

Loaded from `aegis.php` after theme gate passes — see [[architecture]] for full list. Notable:

- `config/patterns.php` — always-on demo patterns
- `config/woocommerce-patterns.php` — `CommercePatternRegistrar::init()`

## Next Steps

- [[architecture]] — Technical architecture
- [[building-assets]] — Build commands
- [[../../themes/aegis/docs/reference/file-structure|Theme File Structure]]
