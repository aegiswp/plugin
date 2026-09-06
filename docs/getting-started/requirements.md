# Requirements

System requirements for the Aegis companion plugin.

## Production Requirements

| Requirement | Minimum Version |
|-------------|-----------------|
| WordPress | 6.9 or later |
| PHP | 7.4 or later |
| MySQL | 5.7 or later (or MariaDB 10.3+) |

### PHP Extensions

- **Required:** `json`, `mbstring`
- **Recommended:** `openssl`, `curl`, `zip`

## Theme Requirements

The Aegis plugin **requires** the Aegis theme. It will not bootstrap on other themes.

| Product | Requirement |
|---------|-------------|
| Aegis plugin | **Aegis theme (required)** |
| Aegis theme | WordPress 7.0+, PHP 8.1+ (WordPress 7.1+ for native Icon library registration) |
| Aegis Pro | Aegis theme ≥ 1.0.0 + free Aegis plugin (recommended) |

Install and activate the Aegis theme **before** activating the plugin.

## Development Requirements

| Requirement | Minimum Version |
|-------------|-----------------|
| Node.js | 20 or later |
| npm | 9 or later |
| Composer | 2 or later (if building from source) |

See [[../development/building-assets|Building Assets]] for build commands.

## Browser Support

Admin UI supports modern browsers (Chrome, Firefox, Safari, Edge — last two major versions).

## Next Steps

- [[installation]] — Install the plugin
- [[../../themes/aegis/docs/getting-started/requirements|Theme Requirements]]
