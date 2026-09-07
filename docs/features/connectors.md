# Connectors

Service credentials and API connections live at **Aegis → Connectors** (`admin.php?page=aegis-connectors`). Plugin compatibility toggles stay at **Aegis → Integrations**.

## Sections

| Section | Anchor | Purpose |
|---------|--------|---------|
| BunnyCDN | `#bunnycdn` | Stream hosting, CDN, storage credentials, and Pro video features |
| Google Maps | `#maps` | Browser and server API keys for the Map block |
| Google Analytics | `#ga4` | GA4 measurement ID, IP anonymization, Consent Mode v2 (Pro) |
| Tag Manager | `#gtm` | GTM container ID, Data Layer (Pro) |
| Clarity | `#clarity` | Microsoft Clarity project ID |
| Plausible | `#plausible` | Domain and optional script URL |
| Fathom | `#fathom` | Site ID |
| Matomo | `#matomo` | Instance URL and site ID |
| Meta Pixel | `#meta-pixel` | Pixel ID and WooCommerce events (Pro) |
| Privacy | `#privacy` | Consent, DNT, Complianz, local scripts, debug (Pro) |

Option keys are unchanged: `aegis_integrations` (toggles), `aegis_bunnycdn`, `aegis_google_maps`, `aegis_analytics`.

Export, import, and reset on this screen use the **integrations** settings group (the same `aegis_integrations` option as **Aegis → Integrations**). Reset also clears `aegis_pattern_control`. API credentials remain excluded from the global export bundle.

## BunnyCDN

The BunnyCDN toggle is available in the free plugin. API fields and stream sub-features require **Aegis Pro**. Video upload and streaming API calls are handled by Pro when BunnyCDN is enabled.

## Maps

The Google Maps panel always loads on Connectors so you can enable the provider and save keys. The browser key is used for the click-to-load Static Maps facade, editor preview, and the interactive JS API. OpenStreetMap is always available as an explicit Map block provider. Automatic OSM fallback when Google has no key is the **OpenStreetMap Fallback** extra at **Aegis → Blocks → Map**.

## Analytics

Each provider has its own Connectors tab. Nothing is sent until you enable a provider and save credentials. Shared consent settings are on **Privacy**. Video performance reports stay on **Aegis → Analytics** (Pro, `aegis-video-analytics`) and are separate from these measurement IDs.

See [[analytics]].

## Next Steps

- [[integrations-dashboard]] — Third-party plugin toggles
- [[analytics]] — Provider settings
- [[../blocks/map]] — Map block
