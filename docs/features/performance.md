# Performance

Site-wide frontend optimizations live at **Aegis → Performance** (`admin.php?page=aegis-performance`).

The page uses a sidebar with **WordPress** (core script cuts), **Core Blocks** (Query Loop gate, embed facades), and **WooCommerce** (storefront script cuts; brand mark icon, same sidebar color as the other items). When WooCommerce is not active, the section status badge shows **Inactive** or **Not Installed** and the toggles are disabled.

WordPress already lazy-loads images and applies LCP `fetchpriority`. Speculative Loading (prefetch/prerender of in-viewport links) is built in since 6.8. Aegis does not duplicate those.

## Where to Configure

| Location | Scope |
|----------|-------|
| **Aegis → Performance → WordPress** | WordPress core script cuts, head/API cleanup, and emoji scripts (Pro) |
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
| Disable Frontend Heartbeat | Off | Dequeues Heartbeat scripts for guests on the frontend |
| Disable XML-RPC | Off | Sets `xmlrpc_enabled` to false and removes the `X-Pingback` header (site-wide, including `xmlrpc.php`) |
| Remove RSD Link | Off | Removes Really Simple Discovery from `wp_head` |
| Remove Shortlink | Off | Removes shortlink from `wp_head` and the `Link` response header |
| Remove Generator Tag | Off | Removes the WordPress generator meta tag and feed generator output |
| Remove REST API Links | Off | Removes REST discovery links from `wp_head`/headers (does **not** disable the REST API) |
| Remove Adjacent Posts Links | Off | Removes previous/next post `rel` links from `wp_head` |
| Remove Emoji Scripts | Off | Strips emoji detection scripts, styles, DNS prefetch, feeds, and TinyMCE emoji on frontend and admin (**Pro**) |

Script cuts (oEmbed, dashicons, heartbeat) and head cleanup apply on the public site only (not wp-admin, cron, or REST requests), except **Disable XML-RPC**, which is site-wide. Emoji removal also runs in admin when Pro is active and the toggle is on.

When Aegis Pro is active, the theme defers frontend emoji removal to **Remove Emoji Scripts**. Standalone theme and free-plugin installs keep zero-base frontend emoji stripping.

## Core Blocks

Enable at **Aegis → Performance → Core Blocks** (`#core-blocks`):

| Toggle | Default | Effect |
|--------|---------|--------|
| Query Loop Performance | Off | Master switch for Pro Query Loop caching, lazy-load (preload + viewport threshold), skeleton loading (AJAX pagination), hover prefetch, and query optimizations (`no_found_rows`, meta hints). **Pro** |
| Embed Facades | Off | Click-to-load YouTube/Vimeo embeds (theme framework). Without the free plugin, the theme enables facades by default. |

Slider lazy-load stays under **Aegis → Blocks → Slider**. It uses Splide `data-src`, not native `loading="lazy"`.

Enabling **Query Loop Performance** alone does not turn on **Blocks → Query Loop** feature extras. The Pro query-builder editor script loads when either that parent or this Performance gate is on.

## WooCommerce

Enable at **Aegis → Performance → WooCommerce** (`#woocommerce`). Detection uses `WooCommerce::is_plugin_active()` (same idea as Connectors extras), not the **Aegis → Integrations → WooCommerce** toggle. The section badge uses Integrations Registry status (**Active** / **Inactive** / **Not Installed**). When WooCommerce is not active, toggles are disabled.

| Toggle | Default | Effect |
|--------|---------|--------|
| Disable Cart Fragments | Off | Dequeues `wc-cart-fragments` outside shop, product, cart, checkout, and account. Keep off if a classic header mini-cart must refresh on every page. Targets the classic script handle; block cart/mini-cart may not use it. |
| Disable Assets Elsewhere | Off | Dequeues WooCommerce scripts/styles on non-shop/product/cart/checkout/account pages |
| Limit Password Strength Meter | Off | Loads password strength scripts only on account and checkout form pages (not the order-received thank-you screen) |

## Query Loop Performance (Pro)

The **Query Loop Performance** toggle unlocks the **Performance (Pro)** sidebar on `core/query` blocks. See [[../../aegis-pro/docs/features/query-performance|Query Performance Pro]].

When the toggle is off, Pro does not apply cache, lazy-load overrides, skeleton, or prefetch. WordPress still lazy-loads Query Loop images. The Performance panel also stays hidden unless the admin gate is on (editor feature flags default off when localization is missing).

## Export / Import / Reset

Performance keys are stored in the `aegis_blocks` option and included in the global export `blocks` group:

| Key | Section |
|-----|---------|
| `perf_disable_wp_embed` | WordPress |
| `perf_disable_dashicons` | WordPress |
| `perf_disable_frontend_heartbeat` | WordPress |
| `perf_disable_xmlrpc` | WordPress |
| `perf_remove_rsd_link` | WordPress |
| `perf_remove_shortlink` | WordPress |
| `perf_remove_generator` | WordPress |
| `perf_remove_rest_api_links` | WordPress |
| `perf_remove_adjacent_posts` | WordPress |
| `perf_remove_emoji` | WordPress (Pro) |
| `embed_facades` | Core Blocks |
| `query_loop_performance` | Core Blocks |
| `perf_woo_disable_cart_fragments` | WooCommerce |
| `perf_woo_disable_assets_elsewhere` | WooCommerce |
| `perf_woo_disable_password_strength` | WooCommerce |

`perf_remove_emoji` and `query_loop_performance` are forced off when Pro is inactive (reads, saves, imports, Blocks reset, and exports). The former `perf_reduce_heartbeat` key migrates to `perf_disable_frontend_heartbeat` once (`aegis_perf_heartbeat_key_v1`). Windows Live Writer / `wlwmanifest` is not offered — core removed that output in 6.3.

**Reset Defaults** on this page turns only those keys off. Reset on **Aegis → Blocks** leaves Performance keys unchanged.

## Relationship to Theme Performance

The theme uses a zero-base loading strategy — see [[../../themes/aegis/docs/features/performance|Theme Performance]] for conditional assets, fonts, and Core Web Vitals guidance.

## Next Steps

- [[../../themes/aegis/docs/features/performance|Theme Performance]]
- [[../../aegis-pro/docs/features/query-performance|Query Performance (Pro)]]
- [[connectors]] — BunnyCDN Stream credentials and extras
- [[integrations-dashboard|Integrations Dashboard]] — Third-party plugin toggles
