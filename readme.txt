=== Aegis ===
Contributors: atmostfear
Tags: blocks, gutenberg, integrations, woocommerce, seo
Requires at least: 6.9
Tested up to: 7.1.3
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom Gutenberg blocks, integrations dashboard, conditionals, code snippets, and analytics. Requires the Aegis block theme.

== Description ==

Aegis is the companion plugin for the [Aegis block theme](https://www.atmostfear-entertainment.com/aegis/). It adds custom blocks, a unified settings dashboard, third-party integrations, display conditionals, code snippets, a hook location catalog, and privacy-friendly analytics.

The plugin requires the Aegis theme. Activate the theme before the plugin.

= Custom blocks =

Enable or disable parent blocks and per-block features from **Aegis → Blocks**:

* Accordion, Counter, Icon, Lightbox, Compare, Map, Marquee, Modal, Newsletter, Query Loop, Related Posts, Slider, SVG, Toggle, Countdown, and Video enhancements
* Schema.org output for FAQ, Event, Local Business, and Video where supported
* Google Maps with OpenStreetMap fallback when no API key is configured

= Integrations dashboard =

Manage third-party plugin compatibility from **Aegis → Integrations**. Each integration can be toggled on or off. Admin notices remind you when a supported plugin is installed but its integration is disabled.

Supported integrations include:

* **E-commerce:** WooCommerce, Easy Digital Downloads, AffiliateWP (dashboard CSS, snippet locations, Pro conditions)
* **LMS:** LearnDash, LifterLMS, Sensei LMS
* **Forms:** Gravity Forms, Ninja Forms, Fluent Forms, Fluent Booking
* **Community:** bbPress (forum styling, Page template wrapper, breadcrumbs, snippet locations)
* **Content:** Co-Authors Plus (multi-author blocks, optional Author Schema)
* **CRM:** FluentCRM (Pro video events), WP Fusion (tags, lists, conditions)
* **SEO:** Rank Math, Yoast SEO, All in One SEO, SEOPress (per-plugin schema delegation and breadcrumb styling; Pro video sitemaps)
* **Developer:** Advanced Custom Fields, Meta Box (field extras, form CSS, bindings), Code Block Pro (theme overlay; no extras), Syntax Highlighting Code Block (theme overlay; no extras)
* **Performance & maps:** Google Maps API settings (browser and server keys) under **Aegis → Connectors**

When an SEO plugin integration is enabled, you can delegate FAQ, Event, Local Business, Video schema, and (with Aegis Pro) video sitemap handling to that plugin instead of the built-in block markup. Each SEO plugin has its own stack description on the Integrations screen.

= Other features =

* **Code Snippets** — Add CSS, JavaScript, HTML, or PHP at theme and integration hook locations (stored in uploads/aegis-snippets/). PHP snippets require an explicit admin opt-in and auto-disable on fatal errors.
* **Conditionals** — Show or hide blocks by user role, capability, device, date, URL, and more
* **Hooks** — Location catalog and instance list for injection points; creating and rendering hook patterns (block CPT) requires **Aegis Pro**
* **Analytics** — Optional, opt-in analytics tags (Google Analytics 4, Google Tag Manager, Plausible, Fathom, Microsoft Clarity, Matomo, Meta Pixel)
* **Modal** — `aegis/modal` block, starter patterns (`aegis/modal-*`), Modals admin tab, and `[aegis_modal id="…"]` shortcode
* **Co-Authors Plus** — Multi-author block rendering when Co-Authors Plus is active. Optional Author Schema extra (off by default) for JSON-LD Person markup.
* **WooCommerce** — Checkout enhancements when WooCommerce is active

= Aegis Pro =

Some integration sub-features (Co-Authors Plus pattern control, BunnyCDN video hosting, advanced query conditions, video sitemap adapters, and additional block features) require the separate **Aegis Pro** plugin. Aegis Pro is not distributed on WordPress.org.

== Installation ==

1. Upload the `aegis` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Open **Aegis** in the admin menu to configure blocks, integrations, conditionals, snippets, and analytics.
4. Activate the Aegis block theme first — the plugin will not load without it.

== Frequently Asked Questions ==

= Do I need the Aegis theme? =

Yes. The companion plugin requires the Aegis theme and will not bootstrap on other themes.

= Do I need Aegis Pro? =

No. The free plugin includes blocks, integrations toggles, code snippets, conditionals, the Hooks location catalog, analytics, and SEO schema delegation. Pro adds hook pattern CPT rendering, advanced video, query conditions, and pattern control.

= Where do I enter Google Maps or analytics credentials? =

Google Maps keys are configured under **Aegis → Connectors → Google Maps**. Analytics providers are configured under **Aegis → Connectors** (one tab per service). Nothing is sent to third-party services until you save credentials and enable a provider.

== External services ==

This plugin can connect to third-party services **only when you configure them** in the admin settings. No data is transmitted by default.

= Google Maps =

* **What:** Geocoding, static maps, and interactive maps in the Map block and editor.
* **When:** When you enter a Google Maps API key and enable the Google Maps integration, or when a map block loads on the frontend.
* **Data sent:** Map coordinates, addresses, and API keys (browser key in frontend scripts; server key used only for server-side geocoding requests).
* **Service:** [Google Maps Platform](https://maps.googleapis.com/) — [Terms](https://cloud.google.com/maps-platform/terms), [Privacy](https://policies.google.com/privacy)

= OpenStreetMap =

* **What:** Map tiles and fallback display when Google Maps is not configured.
* **When:** When the Map block uses OpenStreetMap fallback.
* **Data sent:** Map tile requests (standard browser requests to OpenStreetMap tile servers).
* **Service:** [OpenStreetMap](https://www.openstreetmap.org/) — [Tile usage policy](https://operations.osmfoundation.org/policies/tiles/)

= Analytics providers (optional) =

If you enable analytics under **Aegis → Connectors**, the plugin may load scripts from the provider you choose. Each provider receives data according to its own policy:

* [Google Analytics / Google Tag Manager](https://marketingplatform.google.com/about/analytics/) — [Privacy](https://policies.google.com/privacy)
* [Plausible Analytics](https://plausible.io/) — [Privacy](https://plausible.io/privacy)
* [Fathom Analytics](https://usefathom.com/) — [Privacy](https://usefathom.com/privacy)
* [Microsoft Clarity](https://clarity.microsoft.com/) — [Privacy](https://privacy.microsoft.com/)
* [Matomo](https://matomo.org/) — self-hosted or [Matomo Cloud](https://matomo.org/matomo-cloud/)
* [Meta Pixel](https://www.facebook.com/business/tools/meta-pixel) — [Privacy](https://www.facebook.com/privacy/policy/)

= BunnyCDN (Aegis Pro only) =

BunnyCDN Stream settings can be stored in this plugin’s integrations screen, but video upload and streaming API calls are handled by the separate Aegis Pro plugin when Pro is installed and BunnyCDN is enabled.

* **Service:** [Bunny.net](https://bunny.net/) — [Terms](https://bunny.net/terms), [Privacy](https://bunny.net/privacy)

== Screenshots ==

1. Aegis dashboard with blocks, integrations, and settings tabs
2. Integrations page with third-party plugin toggles
3. Block feature settings for accordion, map, and video blocks

== Changelog ==

= 1.0.0 - 2026-10-14 =
* Initial release.
* Integrations: bbPress under **Community**; Co-Authors Plus under **Content**.
* Integrations: distinct SEO stack descriptions for Rank Math, Yoast, All in One SEO, and SEOPress.
* Modal Modals tab, `[aegis_modal]` shortcode, and `aegis/modal-*` starter patterns.
* Hooks location catalog; hook pattern CPT create/render requires Aegis Pro.
* Repository tooling: Composer/PHPCS, GitHub Actions (CI, release, spelling, stale), `.distignore` release packaging.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
