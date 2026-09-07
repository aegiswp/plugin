# Conditional Logic

The Aegis plugin provides conditional logic that controls block visibility and page-level display rules. Server-side rules remove the block from HTML. Screen size, custom breakpoints, and accessibility rules add CSS classes instead.

## Admin Configuration

Core visibility settings are at **Aegis → Conditionals** (`admin.php?page=aegis-settings`). Turning an extra on shows its controls in the block editor (Visibility extras in the **Visibility** panel, Image Source extras in **Image Source (Pro)** on Featured Image). Turning it off hides the inspector and ignores saved rules of that type.

Plugin-specific extras are **not** on this page. They sit under the matching plugin on **Aegis → Integrations**. Smart Logic **Page / Post Type** has no extra (always available). Visibility Presets are **Aegis → Presets** (Pro).

| Screen | What it gates |
|--------|----------------|
| **Conditionals → Visibility** | Screen size, breakpoints, page type, browser/device, lockdown, query string; Pro cookie, referral, advanced location, post meta |
| **Conditionals → Accessibility** | Reduced motion, screen reader only, color scheme, high contrast, forced colors |
| **Conditionals → User** | Status, role, capability, specific users; Pro user meta |
| **Conditionals → Schedule** | Date & Time, weekdays, daily range, timezone |
| **Conditionals → Image Source** | Pro featured-image sources (content / ACF image / Meta Box image). Not visibility rules. |
| **Integrations → E-commerce** | WooCommerce cart, customer, product; EDD cart, customer, download; AffiliateWP referral, affiliate account, earnings |
| **Integrations → LMS** | LearnDash, LifterLMS, Sensei enrollment/completion/progress (and quiz/group/membership where listed) |
| **Integrations → Forms** | Fluent Forms submissions; Fluent Booking |
| **Integrations → Developer** | **ACF Field** and **Meta Box Field** visibility rules (show/hide a block from a field value). Distinct from Image Source ACF/Meta Box image fields. |
| **Integrations → CRM** | WP Fusion tags and lists |

| Section | Purpose |
|---------|---------|
| Visibility | Screen size, custom breakpoints, page type, browser/device, lockdown, and query string. Cookie, referral, advanced location, and post meta need **Pro**. |
| Accessibility | Reduced motion, screen reader only, color scheme, high contrast, forced colors |
| User | Logged-in status, role, capability, specific user IDs, and user meta. User meta needs **Pro**. |
| Schedule | Date/time, weekdays, daily time range, timezone |
| Image Source Order | Content image by position, plus separate **ACF** and **Meta Box** image field toggles (plugin-gated; **Pro**) |

### Visibility extras

| Extra | What it does |
|-------|----------------|
| Screen Size | Hide on mobile (below 480px), tablet (480–1023px), or desktop (1024px and up) |
| Custom Breakpoints | Hide below and/or above a custom pixel width |
| Page Type | Front page, blog, singular, archive, search, or 404. Off by default; saved page-type rules are ignored until this extra is on, including on hook patterns. |
| Browser & Device | User-agent match for OS, browser, or device class |
| Lockdown | Hide the block from all frontend visitors |
| URL Query String | Match `$_GET` parameters (`is`, `is not`, exists, contains, numeric compares) |
| Cookie | Browser cookie value (**Pro**) |
| Referral Source | Referring domain (**Pro**) |
| Advanced Location | Post type, post IDs, taxonomy term, URL path, archive type (**Pro**) |
| Post Meta | Raw post meta key/value (**Pro**) |

### Accessibility extras

These extras add CSS classes. They do not remove HTML on the server. Turning an extra off hides its inspector controls, skips those classes on render, and does not emit the matching CSS (leftover classes in markup stay inert).

| Extra | What it does |
|-------|----------------|
| Reduced Motion | Hide the block when `prefers-reduced-motion: reduce` |
| Screen Reader Only | Visually hide the block (`aegis-sr-only`); it stays available to assistive tech |
| Color Scheme | Hide the block in light or dark mode. Follows the visitor toggle (`is-style-dark` / `is-style-light`), then the theme default on `body` (`default-mode-dark`, `default-mode-light`, or `default-mode-system`), then the OS `prefers-color-scheme` when dark mode is off |
| High Contrast | Hide the block when `prefers-contrast: more` |
| Forced Colors | Hide the block when `forced-colors: active` (including Windows High Contrast) |

Blocks and page-level Conditionals add these classes to the existing first HTML element (the `core/post-content` wrapper on posts). Hook patterns wrap the whole pattern when there are several top-level blocks.

### Schedule extras

These extras remove HTML on the server when the current time does not match. Turning an extra off hides its inspector controls and ignores saved rules of that type. Schedule is not a Smart Logic field — it stays on the dedicated extras UI.

| Extra | What it does |
|-------|----------------|
| Date & Time | Inclusive start and/or end datetime. Naive values (block `datetime-local` or the hook-pattern picker) have no offset: they are parsed in the schedule timezone when Timezone is on, or in the site timezone when that extra is off or empty — not the browser's local zone. Unparseable values are skipped, same as empty. |
| Days of Week | Limit visibility to selected weekdays (Sunday–Saturday). An empty selection means no weekday restriction. Weekdays use the same timezone as Date & Time. |
| Daily Time Range | Limit visibility to a daily start/end time (`H:i`). If end is before start, the window wraps overnight. The inspector notes this. |
| Timezone | IANA identifier list for Date & Time, daily range, and weekdays. Empty uses the site timezone. Values that are not IANA (abbreviations such as `EST`, offsets such as `UTC+2`) stay in the dropdown labeled invalid so they are not cleared; evaluation uses the site timezone until you pick a listed zone. |

### Image Source extras

These extras are **Pro**. They are not visibility rules and are not Smart Logic fields. They change which image `core/post-featured-image` renders (any template, including Query Loop). The inspector is **Image Source (Pro)** on that block. Settings for the panel are localized on `aegis-pro-image-source` (`aegisImageSource.settings`). Turning an extra off hides that source in the inspector and ignores a saved source of that type (the featured image markup is left as-is; fallbacks are not applied).

Render uses the block’s `context.postId` so Query Loop items resolve the loop post. The editor preview prefers that context over the template’s post ID. Empty ACF/Meta Box URLs are treated as no image.

ACF and Meta Box stay disabled unless that plugin is installed **and** active. Content Image does not need a third-party plugin. **ACF Field** / **Meta Box Field** *visibility* extras stay on **Integrations → Developer**; they do not belong on this tab.

| Extra | What it does |
|-------|----------------|
| Content Image | Use the Nth `<img>` in the post content (1-based). Fallback: featured image, SVG placeholder, or hide the block. If the post has no featured image, Pro still outputs a featured-image figure when a content image is found. |
| ACF Image Field | Use a named ACF image field (array, attachment ID, or URL). Same fallbacks. |
| Meta Box Image Field | Use a named Meta Box image field. Same fallbacks. |

Sites that only stored the old combined Image Source toggle inherit ACF/Meta Box from Content Image until those keys are saved separately.

### User extras

These extras remove HTML on the server when the visitor does not match. Turning an extra off hides its inspector controls and ignores saved rules of that type.

| Extra | What it does |
|-------|----------------|
| User Status | Show only to logged-in or logged-out visitors |
| User Role | Match WordPress roles (`is` / `is not`), with all/any relation. An empty role with **is** matches nobody; with **is not** it matches everyone. |
| User Capability | Match a primitive capability slug such as `edit_posts` (`is` / `is not`), with all/any relation. Typed values are normalized (`Edit Posts` → `edit_posts`). An empty capability matches the same way as an empty role. Object caps like `edit_post` need a post ID and will not match. |
| Specific Users | Match a comma-separated list of user IDs (show or hide) |
| User Meta | Match the current user's meta key/value (**Pro**). Logged-out visitors count as having no meta |

Smart Logic on posts, hook patterns, and snippets can use **User / Logged-in**, **User Role**, and **User Capability** when those extras are on. Those user fields only offer **is** / **is not**. Role values use the site's role list. Capability is a typed slug (`edit_posts`); spaces become underscores. User Meta and Schedule stay on the dedicated extras UI (and the Pro Conditions inspector for User Meta), not in Smart Logic.

Plugin-specific condition defaults (WooCommerce, EDD, AffiliateWP, LMS, Fluent Forms, Fluent Booking, WP Fusion, ACF, Meta Box) are on **Aegis → Integrations**, on the matching category (E-commerce, LMS, Forms, Developer, CRM). Toggles stay disabled unless that plugin is installed **and** active.

Saved preset libraries live at **Aegis → Presets** (Pro). See [[visibility-presets]].

The toolbar supports **Export**, **Import**, and **Reset Defaults** for conditionals settings.

## Block Editor Conditions

Anyone who can edit posts can edit conditions. There is no separate role allow-list.

1. Select any block.
2. In the block settings sidebar, open **Visibility**.
3. Add rules. Enabled extras on a block are combined with AND logic.
4. Save or update the page.

Pro extras appear in a separate **Pro Conditions** panel when those toggles are on. Integration extras (WooCommerce, EDD, AffiliateWP, WP Fusion, and so on) appear in their own inspector panels when those toggles are on.

Block visibility uses the framework **Visibility** block setting plus the plugin **Conditionals Evaluator**. The `visibility` attribute is registered on every block type.

## Condition Types

| Condition | Description |
|-----------|-------------|
| User logged in / out | Authentication state (**User**) |
| User role | WordPress role match (**User**) |
| User capability | Primitive capability slug such as `edit_posts` (**User**). Object caps like `edit_post` will not match. |
| Specific users | User ID list (**User**) |
| User meta | Current user meta key/value (**User**, **Pro**) |
| Page type | Front page, blog, singular, archive, search, 404 (**Visibility**) |
| Screen size / custom width | Viewport CSS (**Visibility**) |
| Reduced motion / screen reader / color scheme / contrast | Preference CSS (**Accessibility**) |
| Browser & device | User-agent match (**Visibility**) |
| Date/time | Naive start/end datetime in the schedule or site timezone (**Schedule → Date & Time**) |
| Days of week | Selected weekdays in that same timezone (**Schedule**) |
| Daily time range | Daily start/end, including overnight windows (**Schedule**) |
| Timezone | IANA zone for schedule extras; empty or non-IANA values use the site timezone (**Schedule**) |
| Image source | Featured Image block source: content image, ACF, or Meta Box (**Image Source**, **Pro**) |
| WooCommerce | Cart, customer history, product context (**Integrations → E-commerce**) |
| Integration conditions | EDD (cart, customer, download), AffiliateWP (referral visit, affiliate account, earnings), LearnDash, LifterLMS, Sensei, Fluent Forms, Fluent Booking (**Integrations** categories; only when that plugin is installed and active) |
| ACF / Meta Box fields | Pro field conditions on **Integrations → Developer** |
| WP Fusion | CRM tags (has / has all / has not) and lists (**Integrations → CRM**) |

## Pro Query Conditions

Advanced block-level query conditions (cookies, ACF, Meta Box, referral URL, WooCommerce customer history, EDD customer/download, AffiliateWP referral/affiliate/earnings, WP Fusion lists, etc.) require **Aegis Pro**. See [[../../aegis-pro/docs/features/query-conditions|Query Conditions]].

## Page-Level Conditions

Post and page visibility uses `_aegis_conditions` post meta on `core/post-content`. Hook patterns use the same payload. The editor shows **Advanced Conditional Logic** (smart groups) plus the enabled **Visibility**, **User**, **Schedule**, and **Accessibility** extras. Viewport and accessibility rules add CSS classes: posts keep the existing `core/post-content` wrapper; hook patterns wrap the pattern output.

## Smart Logic

**Advanced Conditional Logic** on posts, hook patterns, and snippets uses AND groups separated by OR. Fields follow the same extras as block visibility. If every saved field is tied to a disabled extra, Smart Logic is ignored (the snippet or pattern still renders). Posts and hook patterns both evaluate Smart Logic through `Evaluator::should_render_conditions()` (hook patterns call that from `should_render_pattern()`).

| Field | Extra |
|-------|--------|
| Page / Post Type | Always available (this is the current post type, not the Visibility **Page Type** extra) |
| User / Logged-in | **User → User Status** (**is** / **is not**) |
| User Role | **User → User Role** (**is** / **is not**; site role list) |
| User Capability | **User → User Capability** (**is** / **is not**; typed slug such as `edit_posts`) |
| Page / URL | **Visibility → Advanced Location** (Pro) |
| Query Parameter | **Visibility → URL Query String** |
| WP Fusion Tag | **Integrations → CRM → CRM Tags** |
| WP Fusion List | **Integrations → CRM → CRM Lists** |

## Performance

Server-side conditions are evaluated during rendering. Blocks that fail those conditions produce no HTML. Viewport and accessibility rules stay in the HTML and use CSS.

## Caching Considerations

User-specific and time-based conditions (logged-in state, role, capability, schedule, cart, affiliate referral) may require cache exclusions or fragment caching for accurate results.

Hook pattern HTML is cached for one hour when that setting is on. Saving **Aegis → Conditionals** flushes those caches so turning extras off takes effect immediately.

## Next Steps

- [[../../aegis-pro/docs/features/query-conditions|Pro Query Conditions]]
- [[visibility-presets]] — Saved visibility rule sets (Pro)
- [[hook-patterns]] — Inject content at hook locations
- [[code-snippets]] — Snippet Smart Logic
- [[general-settings]] — Export/import conditionals settings
- [[integrations-dashboard]] — Plugin condition toggles
