# Performance

Site-wide frontend optimizations live at **Aegis → Performance** (`admin.php?page=aegis-performance`).

The page uses a sidebar with **WordPress** (core script cuts), **Core Blocks** (Query Loop gate, embed facades), and **WooCommerce** (storefront script cuts; brand mark icon, same sidebar color as the other items).

WordPress already lazy-loads images and applies LCP `fetchpriority`. Speculative Loading (prefetch/prerender of in-viewport links) is built in since 6.8. Aegis does not duplicate those.

## Where to Configure

| Location | Scope |
|----------|-------|
| **Aegis → Performance → WordPress** | WordPress core script cuts and emoji scripts (Pro) |
| **Aegis → Performance → Core Blocks** | Embed facades, Query Loop Pro gate |
| **Aegis → Performance → WooCommerce** | Cart fragments, non-store assets, password strength meter |
| **Aegis → Blocks → Slider → Lazy Loading** | Slider image lazy-load (Pro per-block Splide controls) |
| **Aegis → Connectors → BunnyCDN** | BunnyCDN credentials and stream features |

## WordPress

Enable at **Aegis → Performance → WordPress** (`#wordpress`):

| Toggle | Default | Effect |
|--------|---------|--------|
| Disable oEmbed Script | Off | Removes `wp-embed` and oEmbed discovery on the frontend |
| Disable Dashicons for Guests | Off | Stops dashicons CSS for logged-out visitors |
| Reduce Frontend Heartbeat | Off | Disables Heartbeat scripts for guests on the frontend |
| Remove Emoji Scripts | Off | Strips emoji detection scripts, styles, and DNS prefetch (**Pro**) |

These apply on the public site only (not wp-admin, cron, or REST), except emoji removal which also runs in admin when Pro is active.

## Core Blocks

Enable at **Aegis → Performance → Core Blocks** (`#core-blocks`):

| Toggle | Default | Effect |
|--------|---------|--------|
| Query Loop Performance | Off | Master switch for Pro Query Loop caching, skeleton loading (AJAX pagination), and hover prefetch |
| Embed Facades | Off | Click-to-load YouTube/Vimeo embeds (theme framework) |

Slider lazy-load stays under **Aegis → Blocks → Slider**. It uses Splide `data-src`, not native `loading="lazy"`.

## WooCommerce

Enable at **Aegis → Performance → WooCommerce** (`#woocommerce`). Detection uses `WooCommerce::is_plugin_active()` (same idea as Connectors extras), not the **Aegis → Integrations → WooCommerce** toggle. When WooCommerce is missing, the section shows **Not Installed** and the toggles are disabled.

| Toggle | Default | Effect |
|--------|---------|--------|
| Disable Cart Fragments | Off | Dequeues `wc-cart-fragments` outside cart, checkout, and account. Keep off if a classic header mini-cart must refresh on every page. |
| Disable Assets Elsewhere | Off | Dequeues WooCommerce scripts/styles on non-shop/product/cart/checkout/account pages |
| Limit Password Strength Meter | Off | Loads password strength scripts only on account and checkout |

## Query Loop Performance (Pro)

The **Query Loop Performance** toggle unlocks the **Performance (Pro)** sidebar on `core/query` blocks. See [[../../aegis-pro/docs/features/query-performance|Query Performance Pro]].

When the toggle is off, Pro does not apply cache, skeleton, or prefetch. WordPress still lazy-loads Query Loop images.

## Export / Import / Reset

Performance keys are stored in the `aegis_blocks` option (`embed_facades`, `perf_*`, `perf_woo_*`, `query_loop_performance`) and included in the global export `blocks` group.

**Reset Defaults** on this page turns only those keys off. Reset on **Aegis → Blocks** leaves Performance keys unchanged.

## Relationship to Theme Performance

The theme uses a zero-base loading strategy — see [[../../themes/aegis/docs/features/performance|Theme Performance]] for conditional assets, fonts, and Core Web Vitals guidance.

## Next Steps

- [[../../themes/aegis/docs/features/performance|Theme Performance]]
- [[../../aegis-pro/docs/features/query-performance|Query Performance (Pro)]]
- [[connectors]] — BunnyCDN Stream credentials and extras
- [[integrations-dashboard|Integrations Dashboard]] — Third-party plugin toggles
