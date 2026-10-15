# Aegis Build Commands

## Theme (`wp-content/themes/aegis`)

Templates, patterns, style variations, framework bootstrap, and theme-owned custom blocks.

```bash
cd wp-content/themes/aegis
composer install          # PHP dependencies (framework, utilities)
composer install --no-dev # Theme Directory / production ZIP
npm install
npm run build             # Theme blocks → src/Blocks/
npm run dev               # Watch mode
```

Theme blocks: countdown, slider, slide, toggle, toggle-content, related-posts. Video uses `core/video` (framework enhancements).

Cross-reference: [`wp-content/themes/aegis/BUILD.md`](../themes/aegis/BUILD.md)

## Plugin (`wp-content/plugins/aegis`)

Map, Modal, admin dashboard, and `core/video` editor extensions. Requires the Aegis theme.

```bash
cd wp-content/plugins/aegis
npm ci
composer install          # PHPCS / WPCS (dev only; not shipped in release zips)
npm run build             # Blocks + admin + video editor
npm run build:blocks      # src/Blocks/map and src/Blocks/modal only
npm run build:admin       # assets/admin/build
npm run build:editor      # assets/editor/build/video-editor.js
npm run translate         # languages/aegis.pot (requires `wp` on PATH)
npm run translate:studio  # languages/aegis.pot (WordPress Studio)
composer lint             # PHPCS
```

Development watch:

```bash
npm run start:admin
npm run start:editor
```

Compiled assets are committed for Studio/local use when Node is unavailable. Re-run `npm run build` after changing block or admin sources.

Release packaging uses `.distignore` (and matching `.gitattributes` `export-ignore`). See `.github/WORKFLOWS.md`.

## Verification

With the Aegis theme and plugin active, confirm Map and Modal blocks appear in the inserter and the **Aegis** admin menu loads.

With the theme only (plugin deactivated), theme blocks still register; Map, Modal, and the Aegis dashboard are unavailable.

The plugin does not register on non-Aegis themes.

## Documentation

- [Plugin docs](docs/home.md)
- [Theme docs](../themes/aegis/docs/home.md)
- [Pro docs](../aegis-pro/docs/home.md)
