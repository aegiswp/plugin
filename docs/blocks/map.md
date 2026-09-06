# Map Block

The **Map** block (`aegis/map`) displays interactive maps with Google Maps or OpenStreetMap.

## Overview

| Property | Value |
|----------|-------|
| Block name | `aegis/map` |
| Registered by | Aegis companion plugin |
| Requires | Aegis theme + Aegis plugin |

The theme does not register this block. Enable it (or any Map extra) at **Aegis → Blocks → Map**.

## Configuration

### Google Maps API keys

**Aegis → Connectors → Google Maps**

| Setting | Purpose |
|---------|---------|
| Browser API key | Frontend map tiles, editor preview, and interactions |
| Server API key | Server-side geocoding (address search in the editor) |
| Test connection | Verify key validity |

See [[../features/connectors#maps]].

### OpenStreetMap

You can set the block provider to OpenStreetMap at any time (no API key). **OpenStreetMap Fallback** is separate: when it is on and Google Maps has no key, a block set to Google Maps renders OSM instead. When the fallback is off, that block shows a configuration notice instead of OSM.

## Feature Toggles

Enable extras at **Aegis → Blocks → Map**. They are off on new installs. Existing sites keep previously implicit-on extras.

### Free plugin

| Toggle | What it does |
|--------|----------------|
| Multiple Markers | Extra markers with title/description info windows. The primary location pin always remains. |
| Map Style Presets | Silver, Dark, Retro, Night, and Aubergine Google Maps styles (also applied to the Static Maps facade). |
| Map Controls | Zoom, map type, street view, fullscreen, scroll-wheel, and drag controls. |
| OpenStreetMap Fallback | Use OSM when a Google Maps block has no API key. |
| Schema.org Markup | LocalBusiness JSON-LD from the block’s schema fields (skipped when an SEO plugin owns local business schema). |

### Pro

| Toggle | What it does |
|--------|----------------|
| Directions | Route between origin and destination. |
| Store Locator | Search box and radius circle on the map. |
| Geolocation | Button to show the visitor’s location. |
| Heatmap Layer | Density overlay (loads the Google visualization library). |
| Drawing Tools | Polygon, circle, rectangle, and polyline overlays. |
| KML/GeoJSON Import | External geographic data layers. |
| Dynamic CPT Markers | Markers from a custom post type’s lat/lng meta. |
| Custom Style JSON | Raw Google Maps style array (overrides presets, including the Static Maps facade). |
| Custom Marker Icons | Media-library marker icons (interactive map and editor preview; not the static facade). |
| Marker Clustering | Groups nearby markers at lower zoom levels (vendored MarkerClusterer, no CDN). Editor preview included. |

Pro inspector panels and frontend output follow these toggles. Schema markup is a free feature, not a Pro panel.

## Usage

1. Install and activate the **Aegis theme** and **Aegis plugin**.
2. Enable the Map block extras you need at **Aegis → Blocks → Map**.
3. Configure Maps under **Aegis → Connectors → Google Maps** (optional if you only use OSM).
4. Insert the **Map** block and set location, zoom, and extras.

Google Maps uses a click-to-load facade (static preview with the same style presets as the interactive map, then the JS API). Contact patterns `aegis/form-map` and `aegis/form-map-overlay` register only while the Map block is enabled — see [[../features/plugin-patterns]].

## Rendering

### Click-to-load facade

Google Maps blocks render a Static Maps image until the visitor clicks. That image uses the same style presets as the interactive map. Pro **Custom Style JSON** overrides the preset on the facade when the extra is on and the value is a non-empty JSON array.

Custom marker icons are not drawn on the static image; they apply after the interactive map loads. If adding style rules would make the Static Maps URL longer than about 8192 characters, the facade omits styles so the image still loads.

### Editor preview

The block editor shows the Google Map when a browser API key is configured. Style presets and markers follow the free extras. With Pro extras on, custom icons, custom style JSON, and clustering apply in the editor. Directions, heatmap, and drawing stay frontend-only.

### Marker clustering (Pro)

Clustering uses a vendored MarkerClusterer (`@googlemaps/markerclusterer` 2.5.3) bundled with Aegis Pro. It does not load from a CDN. The script is enqueued when **Marker Clustering** is enabled.

## Next Steps

- [[custom-blocks]] — All plugin blocks
- [[integrations-dashboard]] — Integrations overview
- [[../../aegis-pro/docs/features/block-extensions|Pro Block Extensions]]
- [[../../themes/aegis/docs/blocks/custom-blocks|Theme Custom Blocks]]
