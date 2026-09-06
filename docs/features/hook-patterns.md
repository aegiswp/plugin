# Hook Patterns

**Aegis → Hooks** (`admin.php?page=aegis-hook-patterns`) lists hook patterns on the site. Creating and rendering those patterns requires **Aegis Pro**.

## What You See

**Patterns** (default tab):

- Full-width table of instances: title, location (click-to-copy hook name), priority, **Always / Conditional**, status, **Enable/Disable**, **Edit**, **Delete**
- **Add pattern** — opens the hook pattern editor (Pro). Without Pro it links to License
- Quiet link to **Code Snippets** for PHP/CSS/HTML

**Locations** (`&tab=locations`): collapsed catalog of attach points; click a name to copy it. Enabling locations for snippets is on **Code Snippets → Settings**.

Status **Disabled** means the instance toggle is off. Otherwise status is the post status (Draft, Published, …).

**Conditions** **Always** means the pattern has no visibility rules. **Conditional** (or a rule count) means `_aegis_conditions` is active; click it to edit rules in the pattern editor. Which condition types are available is toggled at **Aegis → Conditionals**.

**Delete** moves the hook pattern to Trash. Enable/Disable sets `_aegis_enabled` without changing publish status.

## Hook Pattern CPT (Pro Required)

- Custom post type: `aegis_hook_pattern`
- New: `post-new.php?post_type=aegis_hook_pattern`
- Frontend rendering: Pro `HookPatternsRenderer`

See [[../../aegis-pro/docs/features/hook-patterns-pro|Hook Patterns Pro]].

## Injection Location Catalog

The free plugin `Injection\LocationRegistry` catalogs attach points including:

- WordPress core hooks (`wp_head`, `wp_footer`, etc.)
- Framework-fired theme hooks (`aegis_before_header`, `aegis_after_content`, etc.)
- Integration-bridged hooks (`aegis_before_woocommerce_checkout`, `aegis_before_gform`, etc.)

Snippets attach to any enabled location in this catalog.

## Framework-Fired Theme Hooks

The Aegis framework fires these hooks automatically (see [[../../themes/aegis/docs/features/hook-patterns|Theme Hook Patterns]]):

- `aegis_before_{template-part-slug}` / `aegis_after_{template-part-slug}`
- `aegis_before_content` / `aegis_after_content`

## Next Steps

- [[code-snippets]] — Inject snippets at hooks
- [[injection-preview]] — Preview hook positions
- [[../../aegis-pro/docs/features/hook-patterns-pro|Pro Hook Pattern Management]]
