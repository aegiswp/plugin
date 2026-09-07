# Architecture

Technical architecture of the Aegis companion plugin.

## Bootstrap

`aegis.php` defines constants, loads Composer autoload, and requires config files in order:

```
config/coauthors.php
config/map.php
config/analytics.php
config/blocks.php / blocks-registrar.php
config/modal.php
config/injection.php / injection-integrations.php
config/hooks.php
config/snippets.php
config/visibility-presets.php
config/conditionals.php / conditionals-renderer.php
config/integrations.php
config/connectors.php
config/seo.php
config/admin.php
config/admin-shell.php
config/editor.php
config/performance.php
config/woocommerce.php
config/patterns.php
config/woocommerce-patterns.php
config/activation-redirect.php
```

On load, the plugin verifies the Aegis theme is active. Without it, bootstrap stops and an admin notice is shown.

`aegis.php` also loads the plugin text domain (`aegis`) from `languages/` on `init`. Plugin UI strings ship in `languages/aegis.pot`. They are not merged into the theme POT. See [[building-assets#translations]].

## Namespace

- **Package namespace:** `Aegis\Plugin\`
- **Mapped to:** `src/`

## Module Overview

| Module | Responsibility |
|--------|----------------|
| `Admin/` | Menu, Renderer, AdminTabs, PageLoader (AJAX screen fragments) |
| `Analytics/` | Tracker, Connectors panel, Settings |
| `Blocks/` | Registrar, AdminPage, Settings (Map/Modal metadata only) |
| `Conditionals/` | Evaluator, AdminPage, PostContentRenderer, SmartConditionsEvaluator, IntegrationsPanel (plugin extras on **Aegis → Integrations**). Posts and hook patterns share `should_render_conditions()`. User extras include status, role, capability (`current_user_can` on a normalized slug), specific users, and Pro user meta. Schedule extras parse naive datetimes with `DateTimeImmutable` in an IANA zone (or the site timezone); non-IANA saved zones fall back to the site timezone. Image Source extras are Pro (`AegisPro\Query\ImageSource`). Viewport/a11y classes go on the existing post-content root (`Visibility::apply_classes()`); hook patterns wrap (`Visibility::wrap()`). |
| `Connectors/` | Connectors admin (BunnyCDN, Maps, Analytics panels) |
| `General/` | General settings admin (SVG upload) |
| `Hooks/` | Hook patterns admin (instance list) |
| `VisibilityPresets/` | Visibility presets admin (library list; CRUD requires Pro) |
| `Injection/` | LocationRegistry, Preview, IntegrationInjector |
| `Integrations/` | Registry, AdminPage, Settings, Notices |
| `Map/` | Map block, Google Maps connectors panel, Static Maps style encoding |
| `Modal/` | Modal block, instance scanner, Modals admin |
| `Seo/` | SEO Manager and plugin adapters |
| `Settings/` | Repository, Controller (AJAX export/import), Migration |
| `Snippets/` | Manager, Storage, Loader, Executors |
| `Patterns/` | `CommercePatternRegistrar` — gated WooCommerce and wishlist patterns |
| `Performance/` | FrontendOptimizations, Performance admin page |

## CommercePatternRegistrar

See [[../features/plugin-patterns#commercepatternregistrar|Plugin Patterns — CommercePatternRegistrar]] for dependency checks, init priority, and slug registration.

## Block Registration Model

| Layer | Registers |
|-------|-----------|
| **Theme** | `aegis/countdown`, `slider`, `slide`, `toggle`, `toggle-content`, `related-posts` |
| **Plugin** | `aegis/map`, `aegis/modal` (+ admin, `core/video` editor extensions) |
| **Pro** | `aegis/tabs`, `tab`, `image-compare` (`src/Blocks/image-compare`); attribute extensions on theme blocks, plugin blocks, `core/video`, and `core/query` |

Video is **`core/video`**, not `aegis/video`. The plugin does not register portable copies of theme blocks.

## Framework Dependency

The plugin reads and writes settings via `Settings\Repository`. The Aegis framework (`vendor/aegis/framework` in the theme) reads these settings for:

- Integration CSS loading
- Block feature enablement (`is_block_enabled()`), including Group Marquee extras
- Schema delegation checks

## Relationship to Theme and Pro

| Layer | Role |
|-------|------|
| Theme | Registers six custom blocks, FSE assets, framework bootstrap, `core/video` enhancements |
| Free plugin | Settings, admin, map/modal, snippets, conditionals, analytics, `core/video` editor scripts, **gated commerce patterns** |
| Pro | Hook pattern CPT, video stack on `core/video`, query pro on `core/query`, Pro-only blocks, block extensions, license |

## Next Steps

- [[building-assets]] — Build commands and translations
- [[file-structure]] — Directory layout
- [[../../themes/aegis/docs/development/architecture|Theme Architecture]]
