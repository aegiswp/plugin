# General Settings

Global plugin settings and settings export/import are available at **Aegis → Settings** (`admin.php?page=aegis-general-settings`).

## Settings on This Page

| Setting group | Description |
|---------------|-------------|
| Media / SVG uploads | SVG upload enable and sanitization options — see [[svg-upload]] |

Performance toggles (oEmbed, dashicons, heartbeat, embed facades, Query Loop Pro gate) are **not** on this page. Configure them at **Aegis → Performance** — see [[performance]].

## Export / Import / Reset

Multiple admin tabs include a toolbar with **Export**, **Import**, and **Reset Defaults**.

### Global Export Bundle (AJAX)

The global export (`wp_ajax_aegis_export_settings`) includes these groups:

| Group | Contents |
|-------|----------|
| `conditional_logic` | Conditionals feature toggles |
| `integrations` | Integration toggle states |
| `blocks` | Block feature toggles |
| `general` | General settings including SVG upload |

**Version:** 1.0.0

### Not Included in Global Export

The following are **excluded** from the global export bundle:

- Code snippets (stored in `uploads/aegis-snippets/`)
- Analytics credentials (`aegis_analytics` option)
- BunnyCDN API keys (`aegis_bunnycdn` option)
- Google Maps API keys
- Pattern control options (`aegis_pattern_control`)
- Per-location snippet enable flags

Export/import snippets and analytics separately through their respective admin pages where available.

### Reset

Reset actions are scoped per group: `conditionals`, `integrations`, `blocks`, `performance`, `general`. Reset restores **all-off** defaults (features are opt-in). Blocks reset leaves Performance keys unchanged.

## Data / Uninstall

A **Data** section on this page controls plugin cleanup:

| Setting | Default | Description |
|---------|---------|-------------|
| Delete data on uninstall | Off | When enabled, deleting the Aegis plugin also deletes settings, snippets, hook patterns, analytics caches, and Pro leftovers. |
| Delete all Aegis data | — | Immediate purge. Type `DELETE` to confirm. Does not remove the plugins. |

Uninstall does **not** delete data unless **Delete data on uninstall** is enabled. Deactivating the plugin never deletes data.

## Next Steps

- [[svg-upload]] — SVG upload settings
- [[performance]] — Site-wide performance toggles
- [[conditional-logic]] — Conditionals export
- [[integrations-dashboard]] — Integrations export
