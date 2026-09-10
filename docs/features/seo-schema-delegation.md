# SEO Schema Delegation

When an SEO plugin integration is enabled, Aegis can delegate structured data output to that plugin instead of emitting schema from block markup.

## Supported SEO Plugins

| Plugin | Integration toggle | Adapter |
|--------|-------------------|---------|
| Rank Math | **Aegis → Integrations → Rank Math** | `Seo\Adapters\RankMathAdapter` |
| Yoast SEO | Integrations | `Seo\Adapters\YoastAdapter` |
| All in One SEO | Integrations | `Seo\Adapters\AioseoAdapter` |
| SEOPress | Integrations | `Seo\Adapters\SeopressAdapter` |
| The SEO Framework | *(no UI toggle)* | `Seo\Adapters\TsfAdapter` (code only) |

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

1. Enable the SEO plugin integration at **Aegis → Integrations → SEO** (Rank Math, Yoast SEO, All in One SEO, or SEOPress). Schema extras stay locked until that plugin is active **and** its integration is on.
2. Enable schema delegation toggles under that plugin (FAQ, Event, Local Business, Video; Video Sitemap needs **Pro**). The `seo_*` keys are shared: the same extras appear under each installed SEO plugin, and runtime uses the currently active SEO plugin plus its integration toggle.
3. When delegation is active, the plugin SEO Manager suppresses duplicate schema from Aegis blocks.
4. The active SEO plugin becomes the canonical schema source.

Co-Authors Plus **Author Schema** (`cap_author_schema` on **Integrations → Content**) is separate. It is off by default and skipped by Enable All. Turn it on only if you want Aegis to emit Person JSON-LD for co-authors; leave it off when Yoast, Rank Math, or another SEO plugin already outputs author schema.

## Breadcrumbs

Aegis delegates breadcrumb schema to the active SEO plugin while supporting the native WordPress Core Breadcrumbs block (`core/breadcrumbs`):
- **Visual markup**: WordPress Core renders `core/breadcrumbs`, styled to match the theme design system tokens (`theme.json`) and stylesheet (`vendor/aegis/framework/public/css/core-blocks/breadcrumbs.css`). The same stylesheet also styles Rank Math’s `.rank-math-breadcrumb`, SEOPress’s `.seopress-breadcrumbs`, and Yoast’s `#breadcrumbs` / `.yoast-breadcrumbs` trails when present on the page. Separator styling targets Rank Math `.separator` and SEOPress `.breadcrumb-sep` only (not `li::after`); Yoast keeps its nested-span text separator and `.breadcrumb_last` current crumb without a forced flex wrapper.
- **Structured data**: The active SEO plugin outputs its own `BreadcrumbList` schema graph in its JSON-LD output. Because Aegis delegates schema to the active SEO plugin, Aegis does not emit competing breadcrumb schema, avoiding schema duplication.
- **Plugin breadcrumbs**: If a site uses another SEO plugin’s proprietary breadcrumb block or shortcode instead of `core/breadcrumbs`, the theme framework preserves and styles standard `.breadcrumb` / `.breadcrumbs` wrappers.

## Developer Filter Hooks

Each SEO adapter exposes a filter hook to allow custom modification of schema data before it is returned:

| SEO Plugin | Filter Hook | Parameters | Description |
|------------|-------------|------------|-------------|
| All in One SEO | `aegis_aioseo_schema_output` | `(array $schema)` | Filters the AIOSEO schema output graph array |
| Rank Math | `aegis_rank_math_json_ld` | `(array $data, mixed $context)` | Filters the Rank Math JSON-LD array |
| Yoast SEO | `aegis_yoast_schema_graph` | `(array $graph, mixed $context)` | Filters the Yoast SEO schema graph array |
| SEOPress | `aegis_seopress_schemas_auto_schema` | `(array $schema, string $type)` | Filters SEOPress automatic FAQ, Event, Local Business, or Video schema JSON (`$type` is `faq`, `event`, `local_business`, or `video`) |

## Video Schema

Basic video schema is handled by the free plugin `Seo\VideoSchema`. Video sitemap adapters require **Aegis Pro**. See [[../../aegis-pro/docs/features/video-stack|Video Stack]].

## Next Steps

- [[integrations-dashboard]] — Enable SEO integrations and Co-Authors Plus Author Schema
- [[../../aegis-pro/docs/features/video-stack|Video Stack]] — Video sitemap adapters
