# SEO Schema Delegation

When an SEO plugin integration is enabled, Aegis can delegate structured data output to that plugin instead of emitting schema from block markup.

## Supported SEO Plugins

| Plugin | Integration toggle | Adapter |
|--------|-------------------|---------|
| Rank Math | **Aegis → Integrations → Rank Math** | `Seo\RankMathAdapter` |
| Yoast SEO | Integrations | `Seo\YoastAdapter` |
| All in One SEO | Integrations | `Seo\AioseoAdapter` |
| SEOPress | Integrations | `Seo\SeopressAdapter` |
| The SEO Framework | *(no UI toggle)* | `Seo\TsfAdapter` (code only) |

## Delegatable Schema Types

When delegation is enabled for an active SEO plugin:

| Schema type | Source blocks |
|-------------|---------------|
| FAQ | Accordion / FAQ blocks |
| Event | Event blocks |
| Local Business | Map / business blocks |
| Video | Video blocks |
| Video Sitemap | **Aegis Pro only** |

## How It Works

1. Enable the SEO plugin integration at **Aegis → Integrations** (Rank Math, Yoast SEO, All in One SEO, or SEOPress).
2. Enable schema delegation toggles on that plugin's tab.
3. When delegation is active, the plugin SEO Manager suppresses duplicate schema from Aegis blocks.
4. The active SEO plugin becomes the canonical schema source.

## Video Schema

Basic video schema is handled by the free plugin `Seo\VideoSchema`. Video sitemap adapters require **Aegis Pro**. See [[../../aegis-pro/docs/features/video-stack|Video Stack]].

## Next Steps

- [[integrations-dashboard]] — Enable SEO integrations
- [[../../aegis-pro/docs/features/video-stack|Video Stack]] — Video sitemap adapters
