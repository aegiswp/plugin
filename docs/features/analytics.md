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
| Matomo | Self-hosted analytics | Free plugin |
| Meta Pixel | Advertising | **Aegis Pro** |
| GA4 Consent Mode v2 | Privacy compliance | **Aegis Pro** |
| GTM Data Layer | Enhanced events | **Aegis Pro** |
| Debug Mode | Admin diagnostics | **Aegis Pro** |

## Privacy Settings

Available for all configured providers:

| Setting | Description |
|---------|-------------|
| Require Consent | Do not load scripts until consent is granted |
| Respect DNT | Honor Do Not Track browser setting |
| Complianz Integration | Work with Complianz consent plugin |
| Local Script Loading | Proxy scripts locally when supported |

## Privacy-First Approach

1. **No tracking by default** — Analytics are disabled until explicitly configured.
2. **Consent awareness** — Optional consent gating for GDPR/CCPA compliance.
3. **Minimal data collection** — Only necessary data points are tracked.
4. **Conditional loading** — Scripts load only when consent is given (if enabled).

## Provider Configuration

### Google Analytics 4

| Setting | Description |
|---------|-------------|
| Measurement ID | Your GA4 measurement ID (G-XXXXXXXXXX). |
| Anonymize IP | Strip the last octet of visitor IP addresses. |

### Google Tag Manager

| Setting | Description |
|---------|-------------|
| Container ID | Your GTM container ID (GTM-XXXXXXX). |

### Meta Pixel (Pro Only)

| Setting | Description |
|---------|-------------|
| Pixel ID | Your Meta pixel identifier. |
| WooCommerce Events | Optional e-commerce event tracking. |

See [[../aegis-pro/docs/features/analytics-pro|Analytics Pro]] for Pro-only analytics features and video analytics.

## Performance

Analytics scripts use `async` or `defer` attributes and load only when a provider is configured and enabled.

## WooCommerce Events

When WooCommerce is active and GA4 or GTM is configured, e-commerce events (`view_item`, `add_to_cart`, `begin_checkout`, `purchase`) can be tracked automatically.

## Verifying Analytics

1. Open your site in a private/incognito window.
2. Open browser developer tools → **Network** tab.
3. Look for requests to your analytics provider domain.
4. Confirm in your provider's real-time dashboard.

## Next Steps

- [[integrations-dashboard]] — Enable WooCommerce and other integrations
- [[../aegis-pro/docs/features/analytics-pro|Analytics Pro]] — Pro analytics features
- [[../../themes/aegis/docs/features/performance|Theme Performance]] — Overall loading strategy
