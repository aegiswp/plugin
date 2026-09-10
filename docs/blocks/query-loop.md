# Query Loop (`core/query`)

Query Loop extras enhance WordPress **`core/query`**. They are not a custom block. Enable them at **Aegis → Blocks → Query Loop**.

## Overview

| Property | Value |
|----------|-------|
| Block name | `core/query` |
| Registered by | WordPress core |
| Enhancements | Aegis framework (theme vendor) + Aegis Pro |
| Toggles | Aegis plugin **Blocks → Query Loop**; Performance at **Aegis → Performance** |

There is no separate parent Query Loop toggle. Enabling any Query Loop extra also turns the parent on (same prefix rule as other Blocks sections). When the plugin is inactive, the framework treats extras as enabled.

Do not confuse this with:

- Theme **`aegis/related-posts`** — a separate custom block under **Aegis → Blocks → Related Posts**
- Pro **Related Posts** on Query Loop — an inspector option on `core/query` (`aegisProRelatedPosts`), not a block variation

## Feature Toggles

Extras are off on new installs. Existing sites keep previously implicit-on free extras.

### Free plugin

| Toggle | What it does |
|--------|----------------|
| Multiple Post Types | Query more than one public post type. |
| Taxonomy Filtering | Filter by category, tag, or custom taxonomy. |
| Include/Exclude Posts | Manually include or exclude post IDs. |
| Custom Field Query | Filter by a custom field key, compare, and value. |
| Order by Custom Field | Sort by a custom field. |
| Extended Ordering | Random (with optional seed), comment count, modified, menu order, author, slug, parent. |
| Responsive Columns | Column counts for mobile, tablet, and desktop. `0` leaves the Query Loop’s own layout **at that breakpoint**. A count greater than `0` adds `aegis-query-cols-mobile`, `aegis-query-cols-tablet`, or `aegis-query-cols-desktop` and forces an Aegis grid only in that viewport (mobile below 782px, tablet 782–1023px, desktop 1024px and up). |
| Gap Controls | Row and column gap on the post template grid. |
| Featured First Post | First post spans multiple columns (needs Responsive Columns or a grid Query Loop). |
| Equal Height Cards | Stretch query cards to equal height. |
| No Results Template | Custom message, style, icon, and optional search form when the query is empty. |

Offset and sticky-post handling have no extra of their own. They apply whenever any Query Loop extra (including Performance) implies the parent is on.

### Pro

| Toggle | What it does |
|--------|----------------|
| Advanced Meta Query | Multiple meta clauses with AND/OR. |
| Date Query | Relative ranges, after/before dates, or a specific year/month/day. |
| Parent/Child Posts | Query children of the current or a specific post. |
| ACF/MetaBox Integration | Field picker in Advanced Meta Query when ACF or Meta Box is active **and** that **Integrations → Developer** toggle is on. |
| AJAX Pagination | Load more, infinite scroll, or AJAX page numbers. |
| Frontend Filters | Taxonomy, search, and sort filters on the frontend. |
| Masonry Layout | CSS or JS masonry grid. |
| Carousel Layout | Slider with navigation, dots, autoplay, and loop. |
| WooCommerce Integration | Product query filters (also requires **Aegis → Integrations → E-commerce → WooCommerce** and WooCommerce active). Attributes still register when those are off so saved settings are not stripped. |

**Query Loop Performance** (caching, lazy-load, skeleton, prefetch) lives at **Aegis → Performance**, not on the Blocks page. See [[../features/performance]].

## Usage

1. Enable the extras you need at **Aegis → Blocks → Query Loop**.
2. Insert a **Query Loop** block.
3. Open the inspector panels that match those extras (Query Parameters, **Layout**, No Results, and Pro panels). Layout lives on the Query Loop inspector, not on the Display panel. Pro panels expose the full settings each module uses, not just an enable toggle.

Layout CSS (`query-layout.css`, class `aegis-query-layout`) loads on the frontend when the block outputs those classes, and in the editor so column/gap/featured/equal-height changes preview on the canvas. No Results CSS (`query-no-results.css`) loads in the editor when the No Results extra is on.

## Rendering

- Framework `QueryEnhancements.php` maps extras to `query_loop_block_query_vars`.
- `QueryLayout.php` adds CSS custom properties and per-breakpoint classes (`aegis-query-cols-*`) on `render_block_core/query`.
- `QueryNoResults.php` replaces an empty post template when No Results is on.
- Editor: `query-enhancements-editor.js` (free) and Pro `query-builder-editor.js`.
- Pro modules keep attributes registered so saved content is not stripped; behavior is gated by extras.

## Next Steps

- [[block-variations]] — Variation and enhancement toggles
- [[../../themes/aegis/docs/blocks/block-variations|Theme Block Variations]]
- [[../../aegis-pro/docs/features/query-loop-pro|Query Loop Pro]]
- [Theme Related Posts](../../themes/aegis/docs/blocks/related-posts.md) — Theme `aegis/related-posts` (not Query Loop)
- [[../features/performance|Performance]] — Query Loop Performance
