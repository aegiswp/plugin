# Aegis Plugin — Agent Instructions

Companion plugin for the Aegis theme. **Will not bootstrap without the Aegis theme active.**

## Project Identity

- **Plugin name:** Aegis
- **Namespace:** `Aegis\Plugin\` → `src/`
- **Text domain:** `aegis`
- **Minimum PHP:** 7.4 (theme requires 8.1)

## Block ownership

| Layer | Registers |
|-------|-----------|
| **Theme** | `aegis/countdown`, `slider`, `slide`, `toggle`, `toggle-content`, `related-posts` |
| **Plugin** | `aegis/map`, `aegis/modal`; admin; `core/video` editor scripts |
| **Pro** | `aegis/tabs`, `tab`, `image-compare` (`src/Blocks/image-compare`); extends `core/video`, `core/query` |

Do **not** register portable copies of theme blocks.

## Pattern ownership

| Directory | Gate |
|-----------|------|
| `patterns/slider/`, `modal/`, `contact/` | Plugin + theme; slider demos also require Slider extras implied on |
| `patterns/author/` | Co-Authors Plus active **and** the integration on |
| `patterns/woocommerce/` | WooCommerce active (`CommercePatternRegistrar`) |
| `patterns/wishlist/` | WooCommerce + TI Wishlist active |
| Pro `patterns/aegis/feature/image-compare.php` | Compare extras implied on (`aegis_patterns`) |

Use `\Aegis\Utilities\Pattern::register_from_file()` for moved commerce patterns (theme slug convention).

## Build

```bash
cd wp-content/plugins/aegis
npm install && npm run build
npm run translate:studio    # languages/aegis.pot (Studio)
```

Theme blocks and the theme POT build in the theme repo. See `docs/development/building-assets.md`.

## Verification

```bash
studio wp eval-file wp-content/themes/aegis/tools/audit-patterns.php
```

## Documentation

- Plugin docs: `docs/home.md`
- Theme docs: `wp-content/themes/aegis/docs/home.md`
