=== Aegis ===
Contributors: atmostfear
Tags: blocks, gutenberg, integrations, woocommerce, seo
Requires at least: 6.9
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom Gutenberg blocks, integrations dashboard, conditionals, hooks, and analytics. Works with any block theme; best with Aegis.

== Description ==

Aegis is the companion plugin for the [Aegis block theme](https://www.atmostfear-entertainment.com/aegis/). It adds custom blocks, a unified settings dashboard, third-party integrations, display conditionals, custom hooks, and privacy-friendly analytics.

The plugin works with any block theme. For the full design system, integration styling, and pattern library, use it with the Aegis theme.

= Custom blocks =

Enable or disable parent blocks and per-block features from **Aegis → Blocks**:

* Accordion, Counter, Icon, Image Lightbox, Map, Marquee, Modal, Newsletter, Query Loop, Related Posts, Slider, SVG, Toggle, Countdown, and Video enhancements
* Schema.org output for FAQ, Event, Local Business, and Video where supported
* Google Maps with OpenStreetMap fallback when no API key is configured

= Integrations dashboard =

Manage third-party plugin compatibility from **Aegis → Integrations**. Each integration can be toggled on or off. Admin notices remind you when a supported plugin is installed but its integration is disabled.

Supported integrations include:

* **E-commerce:** WooCommerce, Easy Digital Downloads, AffiliateWP
* **LMS:** LearnDash, LifterLMS, Sensei LMS
* **Forms:** Gravity Forms, Ninja Forms, Fluent Forms, Fluent Booking
* **Content:** Co-Authors Plus, bbPress, FluentCRM
* **SEO:** Rank Math, Yoast SEO, All in One SEO, SEOPress
* **Developer:** Advanced Custom Fields, Meta Box, Code Block Pro, Syntax Highlighting Code Block
* **Performance & maps:** Google Maps API settings (browser and server keys)

When an SEO plugin integration is enabled, you can delegate FAQ, Event, Local Business, Video schema, and (with Aegis Pro) video sitemap handling to that plugin instead of the built-in block markup.

= Other features =

* **Code Snippets** — Add CSS, JavaScript, HTML, or PHP at theme and integration hook locations (stored in uploads/aegis-snippets/). PHP snippets require an explicit admin opt-in and auto-disable on fatal errors.
* **Conditionals** — Show or hide blocks by user role, capability, device, date, URL, and more
* **Hooks** — Inject block patterns at theme and integration hook points without editing templates
* **Analytics** — Optional, opt-in analytics tags (Google Analytics 4, Google Tag Manager, Plausible, Fathom, Microsoft Clarity, Matomo, Meta Pixel)
* **Co-Authors Plus** — Multi-author block rendering and schema when Co-Authors Plus is active
* **WooCommerce** — Checkout enhancements when WooCommerce is active

= Aegis Pro =

Some integration sub-features (Co-Authors Plus pattern control, BunnyCDN video hosting, advanced query conditions, video sitemap adapters, and additional block features) require the separate **Aegis Pro** plugin. Aegis Pro is not distributed on WordPress.org.

== Installation ==

1. Upload the `aegis` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Open **Aegis** in the admin menu to configure blocks, integrations, conditionals, hooks, and analytics.
4. For the best experience, activate the Aegis block theme. The plugin also works with other block themes.

== Frequently Asked Questions ==

= Do I need the Aegis theme? =

No. Blocks and settings work with any block theme. The Aegis theme adds matching styles, framework integrations, and patterns.

= Do I need Aegis Pro? =

No. The free plugin includes blocks, integrations toggles, code snippets, conditionals, hooks, analytics, and SEO schema delegation. Pro adds advanced video, query, and pattern features.

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

= 1.0.0 =
* Initial WordPress.org release.
* Custom Gutenberg blocks with per-feature toggles.
* Unified integrations dashboard with admin notices for pending integrations.
* Multi-SEO support (Rank Math, Yoast SEO, All in One SEO, SEOPress) with schema delegation.
* Google Maps integration with separate browser and server API keys.
* Conditionals, hooks, analytics, Co-Authors Plus, and WooCommerce features.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
