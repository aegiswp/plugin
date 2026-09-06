# Integrations Dashboard

Manage third-party plugin compatibility from **Aegis → Integrations** (`admin.php?page=aegis-integrations`).

Plugins are grouped by category in the sidebar. Each plugin’s enable toggle, extras, and block-condition switches sit in that category — same sibling-card layout as BunnyCDN features on **Connectors**. Integrations are **off by default** on new installs. Admin notices remind you when a supported plugin is installed but its integration is disabled.

Service credentials (BunnyCDN, Google Maps, analytics providers) live at **Aegis → Connectors**. See [[connectors]].

## Integration Sections

| Section | Anchor | Plugins |
|---------|--------|---------|
| E-commerce | `#ecommerce` | WooCommerce, Easy Digital Downloads, AffiliateWP |
| LMS | `#lms` | LearnDash, LifterLMS, Sensei LMS |
| Forms | `#forms` | Fluent Forms, Fluent Booking, FluentCRM, Gravity Forms, Ninja Forms |
| Content | `#content` | Co-Authors Plus, bbPress |
| SEO | `#seo` | Rank Math, Yoast SEO, All in One SEO, SEOPress |
| Developer | `#developer` | Advanced Custom Fields, Meta Box, Code Block Pro, Syntax Highlighting |
| CRM | `#crm` | WP Fusion |

Plugin hashes (`#woocommerce`, `#wp-fusion`, and so on) still open the matching category.

> **TI WooCommerce Wishlist:** Not listed as an integration toggle. The theme provides the **Wishlist** FSE template (`page-wishlist.html`); the companion plugin registers the `template-page-wishlist` block pattern when WooCommerce and [TI WooCommerce Wishlist](https://wordpress.org/plugins/ti-woocommerce-wishlist/) are active. See [[plugin-patterns#dependency-matrix|Pattern dependency matrix]] and [[../../themes/aegis/docs/features/woocommerce-integration#wishlist-ti-woocommerce-wishlist|Theme WooCommerce Integration]].

> **WooCommerce block patterns:** Registered from `patterns/woocommerce/` by `CommercePatternRegistrar` when WooCommerce is active — not from theme `patterns/`. See [[plugin-patterns]].

WP Fusion **conditions** (tags and lists) are on **Aegis → Integrations → CRM**, under the WP Fusion toggle. The main toggle enables the plugin integration itself.

The same pattern applies to WooCommerce, Easy Digital Downloads, LearnDash, LifterLMS, Sensei LMS, Fluent Forms, Fluent Booking, Advanced Custom Fields, and Meta Box: each plugin is a stacked subheading (same chrome as BunnyCDN’s CDN / Storage / Stream groups) with its enable toggle and extras underneath.

## Schema Delegation

When an SEO plugin integration is enabled, you can delegate FAQ, Event, Local Business, and Video schema to that plugin instead of built-in block markup. See [[seo-schema-delegation]].

Video sitemap delegation requires **Aegis Pro**.

## Pattern Control (Pro)

Several integrations include **Pro** sub-toggles to hide third-party block patterns (WooCommerce, LearnDash, LifterLMS, Sensei, Fluent Forms, Fluent Booking). Pro [[../../aegis-pro/docs/features/pattern-control|Pattern Control]] reads these options.

## Framework Styling

When an integration is enabled, the Aegis framework loads compatibility CSS. See [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]] for styling behavior.

## Next Steps

- [[connectors]] — BunnyCDN, Google Maps, and analytics credentials
- [[analytics]] — Analytics providers
- [[seo-schema-delegation]] — SEO plugin adapters
- [[conditional-logic]] — Integration-specific conditions
