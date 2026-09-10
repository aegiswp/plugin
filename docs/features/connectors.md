# Connectors

Service credentials and API connections live at **Aegis → Connectors** (`admin.php?page=aegis-connectors`). Plugin compatibility toggles stay at **Aegis → Integrations**.

Sidebar and section headers use brand SVG marks (BunnyCDN, Google Maps, GA4, GTM, Clarity, Plausible, Fathom, Matomo, Meta Pixel) with the same `currentColor` treatment as Integrations; Privacy keeps a dashicon.

## Sections

| Section | Anchor | Purpose |
|---------|--------|---------|
| BunnyCDN | `#bunnycdn` | Stream hosting, CDN credentials, and Pro video extras |
| Google Maps | `#maps` | Browser and server API keys for the Map block |
| Google Analytics | `#ga4` | GA4 measurement ID, IP anonymization |
| Tag Manager | `#gtm` | GTM container ID, Data Layer (Pro) |
| Clarity | `#clarity` | Project ID (`[a-z0-9]{6,16}`); official async snippet in head |
| Plausible | `#plausible` | Domain (+ optional script URL); per-host URL strip; defer snippet in head |
| Fathom | `#fathom` | Site ID (+ optional script URL); defer snippet in head |
| Matomo | `#matomo` | Instance URL (`http(s)` / `//` / bare host), Site ID, Privacy Mode; head bootstrap |
| Meta Pixel | `#meta-pixel` | Pixel ID (numeric), WooCommerce events (Pro); head base code |
| Privacy | `#privacy` | Consent Mode v2 (Pro), Require Consent, DNT/GPC, Complianz, local scripts, debug (Pro) |

Option keys are unchanged: `aegis_integrations` (toggles), `aegis_bunnycdn`, `aegis_google_maps`, `aegis_analytics`.

Export and import on this screen use the global settings bundle (including `analytics`). **Reset Defaults** uses the `connectors` group: it turns off BunnyCDN / Google Maps toggles (and BunnyCDN extras), clears `aegis_bunnycdn` and `aegis_google_maps` credentials, clears `aegis_analytics`, and removes locally proxied analytics scripts. Other Integrations dashboard toggles and `aegis_pattern_control` are left unchanged.

## BunnyCDN

The parent **BunnyCDN** toggle is available in the free plugin. API fields and Pro extras require **Aegis Pro**. Helper: `Integrations\BunnyCDN` (`is_plugin_active()`, `is_enabled()`, `is_extra_enabled()`). Theme Stream player CSS loads when the parent toggle is on.

### Pro extras (parent-gated)

| Extra | Key | Behavior |
|-------|-----|----------|
| Stream Library | `bunny_cdn_stream_library` | Browse/select Stream library videos in the block editor |
| Direct Upload | `bunny_cdn_direct_upload` | Upload to Stream from the editor (proxied through WordPress) |
| HLS Streaming | `bunny_cdn_hls_streaming` | HLS.js + quality switcher; off → iframe embed |
| AI Transcription | `bunny_cdn_ai_transcription` | Transcribe/captions REST + webhook auto-transcribe |
| Video Thumbnails | `bunny_cdn_video_thumbnails` | Stream `thumbnail.jpg` poster on HLS players |

### Credentials (`aegis_bunnycdn`)

| Field | Role |
|-------|------|
| Account API Key | Connection test against `api.bunny.net`; Stream REST fallback AccessKey |
| Pull Zone Name | Bunny pull-zone slug for `*.b-cdn.net` playback |
| CDN Hostname | Optional custom CDN host (preferred over pull-zone slug when set) |
| Token Authentication Key | Pull-zone URL token key for **private** HLS signing |
| Stream Library ID | Default library for editor + API lock |
| Stream API Key | Library AccessKey for `video.bunnycdn.com` (preferred over Account key) |
| Webhook Signing Secret | Library **Read-Only** API key for `X-BunnyStream-Signature` HMAC |

Legacy Storage Zone fields have no UI; keys remain in sanitize for backward compatibility.

### Saving and testing credentials

**Save API Settings** and **Test Connection** stay disabled until the Account API Key field has a value (including a masked stored key). Both actions require **Aegis Pro**; they do **not** require the parent BunnyCDN toggle to be on first — you can save and verify keys, then enable the connector. Without Pro, the API panel shows a lock notice instead of the action buttons.

### Security and Pro REST

- Pro routes under `aegis-pro/v1/bunny/*` are **editor-only** (`edit_posts`; uploads also need `upload_files`). They are not public frontend APIs.
- Direct upload is proxied through WordPress so AccessKeys never reach the browser.
- When a library ID is configured, API calls for other libraries are refused.
- Private HLS requires the Token Authentication Key; unsigned private URLs are blocked on the front end.
- Webhooks verify Bunny Stream HMAC (`X-BunnyStream-Signature`) over the raw body; unsigned requests return 403.
- Per-block **Auto Transcribe** and caption language are stored in `aegis_bunny_transcribe_intent` and applied when encoding finishes (status `3`).
- Snippet hooks `aegis_before_video_block` / `aegis_after_video_block` fire when the parent BunnyCDN toggle is on.

### Migrations

- Legacy Pro option `aegis_pro_bunnycdn` maps into `aegis_bunnycdn` (`aegis_bunnycdn_migrated_v1`). The old `token_auth_key` becomes **Token Authentication Key**, not Stream API Key.
- `aegis_bunnycdn_token_key_v2` backfills an empty token field from `stream_api_key` when needed. If Stream API calls fail after that, set **Stream API Key** to the library AccessKey and keep the pull-zone token only in **Token Authentication Key**.

## Maps

The Google Maps panel always loads on Connectors so you can enable the provider and save keys.

| Field | Role |
|-------|------|
| Browser API Key | Static Maps facade, Maps JavaScript, editor preview (HTTP referrer restriction) |
| Server API Key | Editor geocode REST + **Test Connection** (IP restriction) |

**Save API Settings** enables when either key has a value (including a fully masked stored key); **Test Connection** requires the Server API Key. Save/Test do **not** require the Google Maps toggle to be on first — enable the connector separately with **Save Settings**. Partial saves keep unchanged masked secrets. Frontend Google playback still needs the toggle on plus a browser key. OpenStreetMap is always available as an explicit Map block provider. Automatic OSM fallback when Google has no key is the **OpenStreetMap Fallback** extra at **Aegis → Blocks → Map**.

### Maps security

| Control | Behavior |
|---------|----------|
| Key roles | Browser: HTTP referrer; Maps JS + Static Maps (+ Directions API only if you use Pro Directions). Server: IP; Geocoding only. Identical browser/server keys are rejected on save. |
| Editor geocode | `GET aegis/v1/map/geocode` — `edit_posts` only, address max 200 chars, **20 requests/min** per user. Server key never localized to the browser. |
| Directions | Free-text origins/destinations resolve server-side (cached) to `lat,lng` before the public map loads — no browser Geocoder. |
| Store Locator | Matches existing markers by title or accepts `lat,lng`; no public Geocoder. |
| Dynamic markers (Pro) | Public CPTs only; safe meta keys; REST rate-limited for editors. |
| Maps JS libraries | Page-level union so heatmap/drawing siblings load the libraries they need. |

Legacy single `api_key` values and the old Pro Fieldify `aegis[googleMaps]` field migrate once into `browser_api_key` (`aegis_google_maps_migrated_v1`).

## Analytics

Each provider has its own Connectors tab. Nothing is sent until you enable a provider and save credentials. Shared consent settings are on **Privacy**. Video performance reports stay on **Aegis → Analytics** (Pro, `aegis-video-analytics`) and are separate from these measurement IDs.

### Saving analytics settings

Footer **Save Settings** on Connectors posts provider fields via `aegis_save_analytics` into `aegis_analytics`. Fields missing from the request (for example Meta Pixel ID when Pro is inactive, or disabled Pro toggles) keep their stored values. Invalid GA4 (`G-…`), GTM (`GTM-…`), Clarity (`[a-z0-9]{6,16}`), Plausible domain/script URL, Fathom Site ID/script URL (`[A-Z0-9]{5,10}`), Matomo URL/Site ID, or Meta Pixel ID (`\d{5,20}`) values are ignored and surfaced as save warnings. For Clarity, Plausible, Fathom, Matomo, and Meta Pixel, a typo keeps a previously valid credential; if the stored value is also invalid it is cleared. Enabling GA4, GTM, Clarity, Plausible, Fathom, Matomo, or Meta Pixel without usable credentials (normalized where applicable), enabling both GA4 and GTM with IDs set, or Privacy misconfigurations (Consent Mode without GA4/GTM, Require Consent without Complianz Integration, Complianz Integration and/or Require Consent without the plugin, Local Script Loading without GA4/GTM) also surfaces warnings.

### Privacy and Consent Mode

All of these live on the **Privacy** tab (`#privacy`), not on the GA4 or Tag Manager tabs:

| Setting | Behavior |
|---------|----------|
| Consent Mode v2 (Pro) | Emits denied Consent Mode defaults (`wait_for_update: 500`) before gtag/GTM when GA4 and/or GTM are enabled (stored as `ga4_consent_mode`). With Complianz Integration on, Aegis listens for `cmplz_fire_categories` / `cmplz_revoke` (`detail` as a category array or `{ categories }`). Other CMPs can dispatch `aegis_analytics_consent` with `{ analytics, marketing }` or `{ categories: ['statistics','marketing'] }`. |
| Require Consent | Marks scripts as `text/plain` with Complianz data attributes so Complianz can unblock them. Needs **Complianz Integration** and the Complianz plugin (cannot enable when Complianz is missing; can still turn off if previously enabled). Skips GTM and Meta Pixel `<noscript>` fallbacks (they cannot be consent-gated). |
| Respect DNT / GPC | Skips all providers when the request has `DNT: 1` or `Sec-GPC: 1` (`gdpr_respect_dnt`). |
| Complianz Integration | Adds Complianz script attributes and drives Consent Mode updates when that option is on. |
| Local Script Loading | Proxies GA4/GTM scripts through uploads when supported (other providers are not proxied). The GTM head loader always appends the container `id` to the script URL (CDN or local). |
| Debug Mode (Pro) | Loads tracking for admins (bypasses DNT/GPC for that admin) and shows diagnostics. Console lists valid providers plus Consent Mode, Require Consent, Complianz, DNT/GPC, and local scripts. |

Saving Privacy misconfigurations (Consent Mode without GA4/GTM, Require Consent without Complianz Integration, Complianz Integration and/or Require Consent without the plugin, Local Script Loading without GA4/GTM) surfaces warnings.

Admin users do not load tracking scripts unless **Debug Mode** (Pro) is on.

### Tag Manager injection

With Tag Manager enabled and a valid `GTM-…` ID, Aegis prints the official head bootstrap (Consent Mode / Data Layer first when configured), then the container loader. Locally proxied `gtm.js` has no ID in the file URL; the loader adds `id=` at runtime. The noscript iframe uses `ns.html?id=…` on `wp_body_open` (footer fallback if needed).

### Clarity injection

With Clarity enabled and a normalized Project ID (`[a-z0-9]{6,16}`), Aegis prints the official async snippet in `wp_head` and DNS-prefetches `clarity.ms`. Invalid stored IDs never reach the page. Complianz/`Require Consent` uses `data-service="clarity"`.

### Plausible injection

With Plausible enabled and a normalized domain, Aegis prints the official `defer` snippet in `wp_head`. Domain normalization strips scheme/`www.`/paths on each comma-separated host. DNS prefetch uses `plausible.io` or the host of a custom script URL. Invalid stored domains never reach the page; an invalid custom URL falls back to the cloud script until corrected. Use **Custom Script URL** for self-hosted or first-party proxy setups (Local Script Loading does not proxy Plausible). Complianz/`Require Consent` uses `data-service="plausible"`.

### Fathom injection

With Fathom enabled and a normalized Site ID (`[A-Z0-9]{5,10}`), Aegis prints the official `defer` snippet in `wp_head`. DNS prefetch uses `cdn.usefathom.com` or the host of a custom script URL. Invalid stored IDs never reach the page; an invalid custom URL falls back to the cloud script until corrected. Complianz/`Require Consent` uses `data-service="fathom"`. Advanced SPA attributes (`data-spa`, etc.) are not exposed in Connectors.

### Matomo injection

With Matomo enabled and a normalized URL + Site ID, Aegis prints the official tracking bootstrap in `wp_head` and DNS-prefetches the Matomo host. URLs may be pasted as `http(s)://…`, `//…`, or bare host/path (`https` assumed); non-http schemes are rejected and a host is required. Invalid stored credentials never reach the page. Privacy Mode pushes Matomo `setDoNotTrack` + `disableCookies` before pageview. Complianz/`Require Consent` uses `data-service="matomo"`. The one-time `aegis_matomo_privacy_mode_v1` migration renames legacy `matomo_anonymize_ip` — not dead code.

### Meta Pixel injection

With Pro active, Meta Pixel enabled, and a normalized Pixel ID (`\d{5,20}`), Aegis prints the official base code + PageView in `wp_head` and DNS-prefetches `connect.facebook.net`. Invalid stored IDs never reach the page. Optional WooCommerce events fire ViewContent, AddToCart (session after add), InitiateCheckout (cart value/IDs), and Purchase (order value/IDs; deduped via `_aegis_meta_pixel_purchase_tracked`). Complianz/`Require Consent` uses `data-service="facebook"` and `data-category="marketing"`, and skips the noscript image.

See [[analytics]].

## Next Steps

- [[integrations-dashboard]] — Third-party plugin toggles
- [[../aegis-pro/docs/features/video-stack|Video Stack (Pro)]] — BunnyCDN player and editor panels
- [[analytics]] — Provider settings
- [[../blocks/map]] — Map block
