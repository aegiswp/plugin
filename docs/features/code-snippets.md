# Code Snippets

Add custom CSS, JavaScript, HTML, or PHP at theme and integration hook locations without editing theme files.

**Admin:** **Aegis → Code Snippets** (`admin.php?page=aegis-snippets`)

## Storage

Snippets are stored in `uploads/aegis-snippets/` (not in the database export bundle).

## Snippet Types

Pick a type first. The editor and run locations follow that type.

| Type | Runs as | Default locations |
|------|---------|-------------------|
| Functions (PHP) | Included like `functions.php` (requires PHP opt-in) | Everywhere, frontend, admin, login, or a custom injection hook |
| Content | HTML, or mixed PHP + HTML when PHP is enabled | Site header, body open, footer, before/after content, **shortcode**, or a custom hook |
| CSS | Enqueued stylesheet | Frontend, admin, login, or block editor |
| JavaScript | Enqueued script | Frontend/admin header or footer, login, or block editor |

Existing `html` snippets are treated as **Content**. CSS/JS snippets that stored a generic hook name are migrated to the type-aware locations above.

## Snippet Editor

The edit screen is code-first:

- **Title** and **Enabled** in the top bar
- **Type tabs** switch syntax highlighting, linting, location list, and a starter template (`<?php`, content comments, CSS, or a JS IIFE)
- Dark **CodeMirror** editor with language badge, Format, HTML/CSS/JS lint, and PHP `php -l` checks
- **Where to run** uses a dropdown plus a title/description catalog under the editor. Content locations list Shortcode, header, body open, and footer full-width, then Before/After content side by side. Custom injection hooks stay in the dropdown only.
- **Conditional logic** sits under the location chooser
- The sidebar holds group, priority, tags, and description

Search / Export / Import stay on the snippets list, not the editor. Global settings and the custom-location catalog live on the **Settings** tab.

Pasting code that looks like another language **suggests** switching type. The type tab remains the source of truth.

## Shortcodes

Set a Content snippet's run location to **Shortcode**. It no longer runs on its own. Place it with:

```text
[aegis_snippet id="6-office-hours-notice"]
```

The `id` is the snippet filename without the extension. It is derived from the title the first time you save, and does not change if you rename the snippet later. Save once, then copy the shortcode from the editor (copy button next to the tag).

Attributes you add are available inside PHP Content snippets as `$atts`. Enclosed content is `$content`. The snippet may **print** markup or **return** a string/number; printed output wins. If it produces nothing valid, users who can `manage_options` see a short error and everyone else sees nothing.

Conditional logic is evaluated when the shortcode renders. Legacy per-snippet tags such as `[aegis_abc123]` still work if they were already stored.

See [FluentSnippets shortcodes](https://fluentsnippets.com/docs/shortcodes) for the workflow this follows.

## Groups and list filters

Each snippet can have an optional **group** (virtual folder) and **tags**. The list supports [FluentSnippets-style organisation](https://fluentsnippets.com/docs/organising-snippets):

- Search across name, description, tags, and group
- Type tabs with counts, tag filter, Hide inactives
- **Grouped** and **Table** views (remembered in the browser)
- Sort by name, created, updated, or priority
- Per-row status toggle, edit, download (JSON), and delete
- Snippets with a fatal error are highlighted and marked Paused

**Conditional logic** on the editor is off by default. Turn on **Enable Conditional Logic** to build AND groups separated by OR (page/post type, logged-in, role, URL, query string).

## PHP Snippets and Safe Mode

Safe Mode follows the [FluentSnippets kill switch](https://fluentsnippets.com/docs/safe-mode). It is a dedicated section on **Aegis → Code Snippets → Settings**.

| Setting | Description |
|---------|-------------|
| PHP Enabled | Master switch for PHP snippet execution |
| Auto Safe Mode | Enable Safe Mode automatically when a PHP snippet fatals |
| Enable Safe Mode | Disable all snippets and hook pattern output site-wide |
| Safe Mode URL | Secret `?aegis_safe_mode=TOKEN` link that turns Safe Mode on without logging in. Copy and Regenerate sit next to the URL. |
| `AEGIS_DISABLE_SNIPPETS` | `define( 'AEGIS_DISABLE_SNIPPETS', true );` in `wp-config.php` when HTTP is unreachable |

Visiting the Safe Mode URL enables it and redirects to Snippets Settings (login if needed). When it is on, an admin banner offers **Turn off Safe Mode**, unless the constant is defined.

Admin screens still work in Safe Mode so you can pause or fix the offending snippet first. `[aegis_snippet]` shows “Snippets are disabled” to administrators and nothing to everyone else.

## Location Reference

The Snippets **Settings** tab includes an **Available Locations** catalog (groups collapsed by default). Enable or disable each injection hook for **custom** Content/PHP locations. First-class type locations (header, shortcode, CSS frontend, and so on) do not depend on that catalog.

## Snippet Settings

Global snippet settings (PHP opt-in, preview toggles, admin bar shortcuts) are on **Aegis → Code Snippets → Settings**. Safe Mode is a separate section on that tab, with a visible URL, Copy, and Regenerate.

The admin bar Positions item counts enabled hook patterns and snippets and uses singular/plural labels. See [[injection-preview]].

## Next Steps

- [[injection-preview]] — Visual hook preview
- [[hook-patterns]] — Hook catalog
- [[general-settings]] — Settings export scope (snippets excluded)
