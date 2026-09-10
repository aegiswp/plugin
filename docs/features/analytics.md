# Analytics

The Aegis plugin includes a privacy-first analytics framework that supports multiple analytics providers. Nothing is sent to third-party services until you configure and enable a provider.

## Configuration

Analytics settings are in the WordPress admin:

1. Navigate to **Aegis → Connectors**.
2. Open the provider tab (**Google Analytics**, **Tag Manager**, **Clarity**, **Plausible**, **Fathom**, **Matomo**, or **Meta Pixel**).
3. Enable the provider and enter credentials.
4. Configure shared consent options on **Privacy**.
5. Save your settings.

> **Note:** Analytics is not configured under Appearance → Editor → Styles. All analytics settings live on the plugin Connectors screen.

## Supported Providers

| Provider | Type | Availability |
|----------|------|--------------|
| Google Analytics 4 (GA4) | Full analytics | Free plugin |
| Google Tag Manager (GTM) | Tag management | Free plugin |
| Microsoft Clarity | Behavior analytics | Free plugin |
| Plausible | Privacy-focused | Free plugin |
| Fathom | Privacy-focused | Free plugin |
| Matomo | Self-hosted or Matomo Cloud | Free plugin |
| Meta Pixel | Advertising | **Aegis Pro** |
| Consent Mode v2 | Privacy compliance (GA4 and/or GTM) | **Aegis Pro** |
| GTM Data Layer | Page-context `dataLayer` push | **Aegis Pro** |
| Debug Mode | Admin diagnostics (valid credentials; bypasses DNT/GPC for that admin) | **Aegis Pro** |

## Privacy Settings

Shared options on the Connectors **Privacy** tab (`#privacy`):

| Setting | Description |
|---------|-------------|
| Consent Mode v2 (Pro) | Denied defaults (`analytics_storage`, `ad_storage`, `ad_user_data`, `ad_personalization`, `wait_for_update: 500`) before GA4/GTM when those providers load (option key `ga4_consent_mode`). Complianz Integration updates grants from `cmplz_fire_categories` / `cmplz_revoke` (`detail` as a category array or `{ categories }`); other CMPs can dispatch `aegis_analytics_consent`. |
| Require Consent | Hold scripts as `text/plain` with Complianz data attributes until categories are granted. Requires **Complianz Integration** and the Complianz plugin (cannot enable when Complianz is missing; can still turn off if previously enabled). Skips GTM/Meta Pixel noscript fallbacks. |
| Respect DNT / GPC | Skip all tracking when the visitor sends `DNT: 1` or `Sec-GPC: 1` (option key `gdpr_respect_dnt`). Admins with Debug Mode on are not skipped. |
| Complianz Integration | Script attributes for Require Consent + Consent Mode v2 updates from `cmplz_fire_categories` / `cmplz_revoke`. |
| Local Script Loading | Proxy GA4 `gtag.js` / GTM `gtm.js` only (Clarity, Plausible, Fathom, Matomo, Meta are not proxied). GTM loader still appends container `id`. Daily refresh cron. |
| Debug Mode (Pro) | Load tracking for admins (also bypasses DNT/GPC for that admin session); console lists valid providers plus Consent Mode, Require Consent, Complianz, DNT/GPC setting and request headers, and local scripts. |

**Save warnings:** Consent Mode without GA4/GTM configured; Require Consent without Complianz Integration; Complianz Integration and/or Require Consent without the Complianz plugin; Local Script Loading without GA4/GTM.

## Privacy-First Approach

1. **No tracking by default** — Analytics are disabled until explicitly configured.
2. **Consent awareness** — Optional Complianz gating and Pro Consent Mode v2.
3. **Minimal data collection** — Only necessary data points are tracked.
4. **Conditional loading** — With Require Consent + Complianz, scripts stay blocked until categories are granted.

## Provider Configuration

### Google Analytics 4

| Setting | Description |
|---------|-------------|
| Measurement ID | Your GA4 measurement ID (`G-XXXXXXXXXX`). |
| Anonymize IP | Optional legacy `anonymize_ip` gtag flag (GA4 anonymizes IPs by default). |

### Google Tag Manager

| Setting | Description |
|---------|-------------|
| Container ID | Your GTM container ID (`GTM-XXXXXXX`). |
| Data Layer (Pro) | One early `dataLayer.push` with page type (including WooCommerce shop — even as homepage — cart, checkout, order received), post metadata on singulars, and login state — before the container loads. |

**Injection:** The head snippet loads `gtm.js` from Google or the local proxy base URL, then appends the container `id` query arg (required for local files, which do not embed the ID). A `<noscript>` iframe prints on `wp_body_open`, with a footer fallback if that hook never runs. Require Consent skips the noscript iframe.

Consent Mode v2 (Pro, under **Privacy**) also applies to GTM-only setups when GTM is enabled. Prefer either GA4 **or** GTM for the same Google Analytics tags to avoid double-counting; saving both enabled surfaces a warning.

### Microsoft Clarity

| Setting | Description |
|---------|-------------|
| Project ID | From Clarity → **Settings → Overview**. Must match `[a-z0-9]{6,16}` (stored lowercase). |

**Injection:** The official async loader prints in `wp_head` (not the footer). DNS prefetch uses `clarity.ms`. Only IDs that pass normalization are emitted — legacy or invalid stored values are skipped until corrected. Require Consent + Complianz gates the script as `text/plain` with `data-service="clarity"`. Local Script Loading does **not** proxy Clarity (GA4/GTM only).

**Save warnings:** Enabling without a usable Project ID warns (including when a leftover invalid ID fails normalization). An invalid submitted ID is ignored: a previously valid ID is kept; if the stored value is also invalid it is cleared.

### Plausible

| Setting | Description |
|---------|-------------|
| Domain | Site domain from Plausible. Pasted `https://` / `www.` / paths are stripped **per host**; comma-separate multiple hosts (e.g. `https://a.com, https://b.com`). Stored lowercase. ASCII hostnames (or `localhost`). |
| Custom Script URL | Optional self-hosted or first-party proxy script (`http`/`https`). Blank uses `https://plausible.io/js/script.js`. |

**Injection:** Official `defer` snippet prints in `wp_head`. DNS prefetch uses `plausible.io` or the custom script host. Only normalized domains are emitted; invalid stored domains never reach the page. If a stored custom URL fails validation at runtime, the cloud script URL is used until the setting is fixed. Require Consent + Complianz uses `data-service="plausible"`. Local Script Loading does **not** proxy Plausible — use Custom Script URL for first-party proxying.

**Save warnings:** Enabling without a usable domain warns (including when a leftover invalid domain fails normalization). Invalid domain/URL keep a prior valid value or clear if stored value is also invalid.

### Fathom

| Setting | Description |
|---------|-------------|
| Site ID | From Fathom → Settings → Sites (`[A-Z0-9]{5,10}`; stored uppercase, e.g. `ABCDEFG` or `ABCD1234`). |
| Custom Script URL | Optional custom-domain or proxied script (`http`/`https`). Blank uses `https://cdn.usefathom.com/script.js`. |

**Injection:** Official `defer` snippet prints in `wp_head`. DNS prefetch uses `cdn.usefathom.com` or the custom script host. Only normalized Site IDs are emitted. If a stored custom URL fails validation at runtime, the cloud script URL is used until the setting is fixed. Require Consent + Complianz uses `data-service="fathom"`. Local Script Loading does **not** proxy Fathom. Site-wide **Respect DNT / GPC** already skips Fathom (no separate `data-honor-dnt`). SPA/`data-spa` advanced attrs are not exposed — configure SPA tracking in Fathom or via a custom snippet if needed.

**Save warnings:** Enabling without a usable Site ID warns. Invalid ID/URL keep a prior valid value or clear if stored value is also invalid.

### Matomo

| Setting | Description |
|---------|-------------|
| Matomo URL | Instance base URL. Accepts `http(s)://…`, protocol-relative `//…`, or bare host/path (`https` assumed). Explicit non-http schemes are rejected. Host required; trailing slash stripped. Self-hosted or Matomo Cloud (`https://your-site.matomo.cloud`). |
| Site ID | Positive integer site ID from Matomo (`[1-9][0-9]{0,9}`). |
| Privacy Mode | Browser Do Not Track + disable Matomo cookies (`matomo_privacy_mode`; legacy `matomo_anonymize_ip` migrates on read/save and via one-time `aegis_matomo_privacy_mode_v1` migration). Configure IP anonymization in Matomo admin. |

**Injection:** Official-style tracking bootstrap prints in `wp_head` (`trackPageView`, `enableLinkTracking`, `setSiteId` as integer, `matomo.php` / `matomo.js`). DNS prefetch uses the Matomo host. Only normalized URL + Site ID pairs are emitted. Require Consent + Complianz uses `data-service="matomo"`. Local Script Loading does **not** proxy Matomo (use a first-party reverse proxy on your Matomo host if needed).

**Save warnings:** Enabling without a usable URL and/or Site ID warns with a specific message for each gap (both missing, URL only, or Site ID only). Invalid URL/Site ID keep a prior valid credential or clear if the stored value is also invalid.

### Meta Pixel (Pro Only)

| Setting | Description |
|---------|-------------|
| Pixel ID | Numeric Meta Pixel ID from Events Manager (`\d{5,20}`). Invalid values keep a prior valid ID or clear if stored value is also invalid. |
| WooCommerce Events | When WooCommerce is active (`WooCommerce::is_plugin_active()`, not the Integrations toggle): **ViewContent** on product pages; **AddToCart** after `woocommerce_add_to_cart` (session payload, emitted once on the next HTML view); **InitiateCheckout** on checkout (cart value, currency, content IDs including variations); **Purchase** on order received (order total, currency, content IDs including variations; deduped per order via `_aegis_meta_pixel_purchase_tracked`). |

**Injection:** Official base code + PageView print in `wp_head` (Pro gated). DNS prefetch uses `connect.facebook.net`. Only normalized Pixel IDs are emitted. Require Consent + Complianz uses `data-service="facebook"` / `data-category="marketing"` and skips the `<noscript>` image (it cannot be consent-gated). Local Script Loading does **not** proxy Meta.

**Save warnings:** Enabling without a usable Pixel ID warns. Invalid Pixel IDs keep a prior valid credential or clear if the stored value is also invalid.

See [[../aegis-pro/docs/features/analytics-pro|Analytics Pro]] for Pro-only analytics features and video analytics.

## Performance

Analytics scripts use `async` or `defer` attributes and load only when a provider is configured and enabled.

## WooCommerce Events

Meta Pixel (Pro) fires ViewContent, AddToCart (session-captured after add-to-cart, emitted on the next HTML view), InitiateCheckout, and Purchase when **WooCommerce Events** is on at **Aegis → Connectors** and `WooCommerce::is_plugin_active()` (`WooCommerce`, `WC()`, or `WC_VERSION`). That check is Connectors detection, not the **Aegis → Integrations → E-commerce → WooCommerce** toggle. AJAX add-to-cart without a full navigation still queues AddToCart for the following page load. Purchase is written once per order (`_aegis_meta_pixel_purchase_tracked`) so thank-you refreshes do not double-count; if Require Consent blocks the thank-you scripts and consent is never granted on that view, Purchase is not retried later.

## Verifying Analytics

1. Open your site in a private/incognito window (admin users skip tracking unless **Debug Mode** is on; Debug Mode also bypasses DNT/GPC for that admin).
2. Open browser developer tools → **Network** tab.
3. Look for requests to your analytics provider domain.
4. Confirm in your provider's real-time dashboard.

With **Debug Mode** (Pro) on, admins also see console lines listing providers with valid credentials plus Consent Mode, Require Consent, Complianz, DNT/GPC, and local-script state.

## Export and reset

Provider settings live in `aegis_analytics` and are included in the global settings export. **Reset Defaults** on **Aegis → Connectors** clears analytics along with BunnyCDN/Maps connector toggles and credentials — see [[connectors]] and [[general-settings]].

## Next Steps

- [[integrations-dashboard]] — Enable WooCommerce and other integrations
- [[../aegis-pro/docs/features/analytics-pro|Analytics Pro]] — Pro analytics features
- [[../../themes/aegis/docs/features/performance|Theme Performance]] — Overall loading strategy
