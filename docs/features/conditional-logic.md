# Conditional Logic

The Aegis plugin provides conditional logic that controls block visibility and page-level display rules. Conditions are evaluated server-side — hidden blocks are removed from HTML output, not hidden with CSS.

## Admin Configuration

Core visibility settings are at **Aegis → Conditionals** (`admin.php?page=aegis-settings`).

| Section | Purpose |
|---------|---------|
| Visibility | Screen size, request context, location, and post meta. Cookie, referral, advanced location, and post meta need **Pro**. |
| Accessibility | Accessibility-related rules |
| User | User role, authentication, and user meta. User meta needs **Pro**. |
| Schedule | Date/time defaults |
| Image Source Order | Content image by position, plus separate **ACF** and **Meta Box** image field toggles (plugin-gated; **Pro**) |

Plugin-specific condition defaults (WooCommerce, EDD, LMS, Fluent Forms, Fluent Booking, WP Fusion, ACF, Meta Box) are on **Aegis → Integrations**, on the matching category (E-commerce, LMS, Forms, Developer, CRM). Toggles stay disabled unless that plugin is installed **and** active.

Saved preset libraries live at **Aegis → Presets** (Pro). See [[visibility-presets]].

The toolbar supports **Export**, **Import**, and **Reset Defaults** for conditionals settings.

## Block Editor Conditions

Anyone who can edit posts can edit conditions. There is no separate role allow-list.

1. Select any block or Group block.
2. In the block settings sidebar, open **Conditions** (under Advanced).
3. Add conditions with AND logic within a single block.
4. Save or update the page.

Block visibility uses the framework **Visibility** block setting plus the plugin **Conditionals Evaluator**.

## Condition Types

| Condition | Description |
|-----------|-------------|
| User logged in / out | Authentication state |
| User role | WordPress role match |
| Page type | Front page, single, archive, 404, etc. |
| Device type | Viewport breakpoint |
| Date/time | Schedule-based visibility |
| WooCommerce | Cart status, products in cart (**Integrations → E-commerce**) |
| Integration conditions | EDD, LearnDash, LifterLMS, Sensei, Fluent Forms, Fluent Booking (**Integrations** categories; only when that plugin is installed and active) |
| ACF / Meta Box fields | Pro field conditions on **Integrations → Developer** |
| WP Fusion | CRM tags and lists (**Integrations → WP Fusion**) |

## Pro Query Conditions

Advanced block-level query conditions (cookies, ACF, Meta Box, referral URL, WooCommerce customer history, etc.) require **Aegis Pro**. See [[../../aegis-pro/docs/features/query-conditions|Query Conditions]].

## Page-Level Conditions

Post and page visibility uses `_aegis_conditions` post meta on `core/post-content`. Configure via the block editor or post meta box (when enabled).

## Performance

Conditions are evaluated during server-side rendering. Blocks that fail conditions produce no HTML output and load no associated assets.

## Caching Considerations

User-specific conditions (logged-in state, role, cart) may require cache exclusions or fragment caching for accurate results.

## Next Steps

- [[../../aegis-pro/docs/features/query-conditions|Pro Query Conditions]]
- [[visibility-presets]] — Saved visibility rule sets (Pro)
- [[hook-patterns]] — Inject content at hook locations
- [[general-settings]] — Export/import conditionals settings
- [[integrations-dashboard]] — Plugin condition toggles
