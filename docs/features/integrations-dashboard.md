# Integrations Dashboard

Manage third-party plugin compatibility from **Aegis → Integrations** (`admin.php?page=aegis-integrations`).

Plugins are grouped by category in the sidebar (alphabetical). Each plugin’s enable toggle, extras, and block-condition switches sit in that category as a flat settings list (same row layout as **Blocks**). Stacked plugins in a category are listed alphabetically. Integrations are **off by default** on new installs, and a toggle can only be turned on when that plugin is installed and active. Admin notices remind you when a supported plugin is installed but its integration is disabled.

Service credentials (BunnyCDN, Google Maps, analytics providers) live at **Aegis → Connectors**. See [[connectors]].

## Integration Sections

| Section | Anchor | Plugins |
|---------|--------|---------|
| Content | `#content` | bbPress, Co-Authors Plus |
| CRM | `#crm` | FluentCRM, WP Fusion |
| Developer | `#developer` | Advanced Custom Fields, Code Block Pro, Meta Box, Syntax Highlighting |
| E-commerce | `#ecommerce` | AffiliateWP, Easy Digital Downloads, WooCommerce |
| Forms | `#forms` | Fluent Booking, Fluent Forms, Gravity Forms, Ninja Forms |
| LMS | `#lms` | LearnDash, LifterLMS, Sensei LMS |
| SEO | `#seo` | All in One SEO, Rank Math, SEOPress, Yoast SEO |

Plugin hashes (`#woocommerce`, `#wp-fusion`, and so on) still open the matching category.

> **TI WooCommerce Wishlist:** Not listed as an integration toggle. The theme provides the **Wishlist** FSE template (`page-wishlist.html`); the companion plugin registers the `template-page-wishlist` block pattern when WooCommerce and [TI WooCommerce Wishlist](https://wordpress.org/plugins/ti-woocommerce-wishlist/) are active. See [[plugin-patterns#dependency-matrix|Pattern dependency matrix]] and [[../../themes/aegis/docs/features/woocommerce-integration#wishlist-ti-woocommerce-wishlist|Theme WooCommerce Integration]].

> **WooCommerce block patterns:** Registered from `patterns/woocommerce/` by `CommercePatternRegistrar` when WooCommerce is active — not from theme `patterns/`. See [[plugin-patterns]].

WP Fusion **conditions** (CRM Tags and CRM Lists) are on **Aegis → Integrations → CRM**, under the WP Fusion toggle. The main toggle enables the plugin integration itself. Tags add has / has all / has not rules; lists add has / has not list rules. Either extra also enables CRM logged-in checks.

Easy Digital Downloads extras under **E-commerce** are Cart Conditions, Customer History, and Download Context. WooCommerce extras in the same section are Cart Conditions, Customer History, and Product Context. AffiliateWP extras are Referral Visit, Affiliate Account, and Earnings. Each extra has its own inspector section and frontend rules. Extras stay disabled unless that plugin is installed and active **and** the parent integration toggle is on. The AffiliateWP integration toggle still enables theme styles and snippet injection locations (`aegis_before_affwp_dashboard` / `aegis_after_affwp_dashboard`).

The same pattern applies to WooCommerce, LearnDash, LifterLMS, Sensei LMS, Fluent Forms, Fluent Booking, Advanced Custom Fields, and Meta Box: each plugin is a stacked subheading with its enable toggle and extras underneath. Pattern-control extras (WooCommerce Patterns / Templates, LearnDash Patterns, and so on) stay disabled unless that plugin is active and the parent integration is on.

**ACF / Meta Box conditions vs Image Source:** **ACF Field** and **Meta Box Field** on **Integrations → Developer** show or hide a block from a field value (Pro Conditions inspector). **ACF Image Field** and **Meta Box Image Field** on **Aegis → Conditionals → Image Source** only change which file `core/post-featured-image` displays. See [[conditional-logic#image-source-extras]].

## Schema Delegation

When an SEO plugin integration is enabled, you can delegate FAQ, Event, Local Business, and Video schema to that plugin instead of built-in block markup. See [[seo-schema-delegation]].

Video sitemap delegation requires **Aegis Pro**.

## Pattern Control (Pro)

Several integrations include **Pro** sub-toggles to hide third-party block patterns (WooCommerce, LearnDash, LifterLMS, Sensei, Fluent Forms, Fluent Booking). Pro [[../../aegis-pro/docs/features/pattern-control|Pattern Control]] reads these options. **Reset Defaults** on Integrations (or Connectors) also clears `aegis_pattern_control`.

## Framework Styling

When an integration is enabled, the Aegis framework loads compatibility CSS. See [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]] for styling behavior.

## Next Steps

- [[connectors]] — BunnyCDN, Google Maps, and analytics credentials
- [[analytics]] — Analytics providers
- [[seo-schema-delegation]] — SEO plugin adapters
- [[conditional-logic]] — Integration-specific conditions
