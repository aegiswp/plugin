# Plugin Patterns

The Aegis plugin registers block patterns from `patterns/` when loaded. Always-on patterns use `config/patterns.php`; WooCommerce and wishlist patterns use `config/woocommerce-patterns.php` with dependency gates.

## Pattern Directories

| Directory | Patterns | When registered |
|-----------|----------|-----------------|
| `patterns/slider/` | Slider demonstration patterns | Slider block implied on |
| `patterns/modal/` | Modal demonstration patterns | Modal block enabled |
| `patterns/contact/` | Contact form layouts (includes Map block) | Plugin + theme active |
| `patterns/author/` | Co-authors layout patterns | Co-Authors Plus active **and** the integration on |
| `patterns/woocommerce/` | Shop templates, product layouts, WC headers, checkout/cart patterns | **WooCommerce active** |
| `patterns/wishlist/` | Wishlist page pattern (`template-page-wishlist`) | **WooCommerce + TI Wishlist active** |

## Dependency Matrix

| Pattern set | Requires |
|-------------|----------|
| Slider | Aegis theme + Aegis plugin + Slider extras implied on |
| Modal patterns | Above + Modal block enabled |
| Map contact patterns | Above + Map block enabled |
| `patterns/author/` | Co-Authors Plus active **and** the integration on |
| `patterns/woocommerce/**` | Above + **WooCommerce** |
| `patterns/wishlist/**` | Above + **TI WooCommerce Wishlist** |

Commerce patterns use the same slug convention as theme patterns (`template-page-cart`, `header-default`, etc.) via `\Aegis\Utilities\Pattern::register_from_file()` so FSE templates that reference pattern slugs continue to resolve.

The theme does **not** ship copies of those WooCommerce patterns. The exception is a Woo-free `header/default` for `parts/header.html` when WooCommerce is inactive; the plugin overlays the mini-cart variant of `header-default` when WooCommerce is active.

**FSE templates** (cart, checkout, wishlist, etc.) remain in the **theme** `templates/` directory. They are hidden from the Site Editor when dependencies are inactive; companion patterns register only when dependencies are active.

Generic commerce marketing patterns without WooCommerce blocks (trust badges, category grids with core blocks) stay in the theme.

## CommercePatternRegistrar

`src/Patterns/CommercePatternRegistrar.php` (`config/woocommerce-patterns.php`):

- Hooks `init` at **priority 12** (after the theme unregisters `woocommerce/` plugin patterns at priority 11)
- **`is_woocommerce_active()`** — `WooCommerce::is_plugin_active()` (`WooCommerce`, `WC()`, or `WC_VERSION`)
- **`is_ti_wishlist_active()`** — `defined( 'TINVWL_VERSION' )` or `function_exists( 'tinvwl_get_wishlist' )`
- Recursively scans `patterns/woocommerce/` when WooCommerce is active
- Scans `patterns/wishlist/` when both WooCommerce and TI Wishlist are active
- Skips dot-prefixed PHP files; registers via `\Aegis\Utilities\Pattern::register_from_file()` for slug parity with theme FSE templates

## Pattern audit

The theme tool `tools/audit-patterns.php` scans plugin `patterns/woocommerce/` and `patterns/wishlist/` for slug integrity. See [[../../themes/aegis/docs/development/tools|Theme Development Tools]].

## Pro Patterns

Marketing and premium layout patterns live in **Aegis Pro**. See [[../../aegis-pro/docs/patterns/pro-patterns|Pro Patterns]].

Do not duplicate Pro pattern slugs in the theme or free plugin.

Contact form+map layouts (`form-map`, `form-map-overlay`) live in this plugin and register only when the Map block is enabled. Do not copy them into the theme.

Related Posts grid, list, cards, and minimal layouts live in the **theme** (`patterns/blog/related-posts-*.php`, slugs `blog-related-posts-*`). They unregister when **Aegis → Blocks → Related Posts** is implied off. Do not copy them into this plugin.

## Next Steps

- [[../../themes/aegis/docs/using-the-theme/block-patterns|Theme Block Patterns]]
- [[../../aegis-pro/docs/patterns/pro-patterns|Pro Patterns]]
