# Building Assets

The Aegis plugin builds Map and Modal blocks, admin dashboard assets, and `core/video` editor extensions.

## Commands

```bash
cd wp-content/plugins/aegis
npm install
npm run build             # Admin + video editor (does not compile Map/Modal)
npm run build:admin       # assets/admin/build
npm run build:editor      # assets/editor/build/video-editor.js
```

`npm run build:blocks` still exists but **overwrites** the hand-maintained Map and Modal `index.js` / `view.js` IIFEs. Do not run it unless you intend to replace those sources with a webpack bundle.

Development watch:

```bash
npm run start:admin
npm run start:editor
```

## What Gets Built

| Output | Source |
|--------|--------|
| Map, Modal block assets | Hand-maintained in `src/Blocks/map/` and `src/Blocks/modal/` (`index.js`, `view.js`, `style.css`) |
| Admin dashboard | `assets/admin/` |
| Video editor extension | `assets/editor/` (extends `core/video`) |

The plugin does **not** ship portable copies of theme blocks. Theme-owned blocks are built in the theme repository.

## Theme Blocks

Theme-owned blocks (countdown, slider, slide, toggle, toggle-content, related-posts) are built in the **theme** repository:

```bash
cd wp-content/themes/aegis
npm run build
```

See [[../../themes/aegis/docs/development/building-assets|Theme Building Assets]].

## Compiled Assets in Git

Compiled assets are committed for environments without Node. Re-run `npm run build` after changing block or admin sources.

## Translations

The free plugin ships `languages/aegis.pot` (text domain `aegis`). Do not put plugin strings in the theme POT.

```bash
cd wp-content/plugins/aegis
npm run translate             # Requires `wp` on PATH
npm run translate:studio      # WordPress Studio (Windows)
```

`wp i18n make-pot` scans PHP and JavaScript (`--skip-block-json`, same as the theme). Admin Smart Logic strings live in `assets/admin/smart-conditions.js`. The post/hook-pattern extras UI (`assets/admin/hook-patterns-conditions.js`) is scanned for wrapped `wp.i18n` strings. It does not scan TypeScript, so run `npm run build:editor` first if you changed `assets/editor/video-editor.tsx` — those strings come from `assets/editor/build/video-editor.tsx.js`.

Theme strings: `wp-content/themes/aegis/languages/aegis.pot`. Pro strings: `wp-content/plugins/aegis-pro/languages/aegis-pro.pot`.

## Verification

With the Aegis theme and plugin active:

- Map and Modal blocks appear in the block inserter
- The **Aegis** admin menu loads without errors
- Theme blocks (countdown, slider, etc.) appear from the theme build

## Next Steps

- [[architecture]] — Plugin structure
- [[../../themes/aegis/docs/development/building-assets|Theme Build]]
