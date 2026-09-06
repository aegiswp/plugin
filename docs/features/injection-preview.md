# Injection Preview

Visualize injection hook and snippet positions on the frontend while developing.

## Enabling Preview

Navigate to **Aegis → Code Snippets → Settings**.

| Setting | Description |
|---------|-------------|
| Show hook positions | Highlight registered hook locations on the frontend |
| Show snippet positions | Highlight active snippet injection points |
| Admin bar shortcuts | Add a Positions item to the frontend admin bar |

Settings are stored in user meta per administrator.

## Admin Bar Menu

When logged in as an administrator with **Admin bar shortcuts** on, the frontend toolbar shows the Aegis brand mark as a WordPress `ab-icon` plus a count label:

| Item | Label |
|------|--------|
| Parent | **1 Position** / **n Positions** — enabled hook patterns + enabled snippets. Tooltip: **Aegis Position** / **Aegis Positions**. Links to **Aegis → Code Snippets**. |
| Hooks | **1 Hook** / **n Hooks** with **(On)** or **(Off)**. Tooltip: Show/Hide hook positions. |
| Snippets | **1 Snippet** / **n Snippets** with **(On)** or **(Off)**. Tooltip: Show/Hide snippet positions. |

Zero uses the plural form (`0 Positions`, `0 Hooks`, `0 Snippets`), matching WordPress `_n()`.

The item follows core toolbar metrics (`ab-icon`, `ab-label`, 13px / 400, hover `#72aee6`). Do not restyle it as a custom flex chip or inject extra HTML after the node (`meta.html` prints after the item).

## How It Works

`Injection\Preview` runs on the frontend when preview settings are enabled. It does not affect visitors who are not administrators with preview enabled.

## Related Features

- [[code-snippets]] — Attach snippets to locations
- [[hook-patterns]] — Hook reference catalog
- [[../../themes/aegis/docs/features/hook-patterns|Theme Injection Hooks]] — Framework-fired hooks

## Next Steps

- [[code-snippets]] — Create snippets at hook locations
- [[../../aegis-pro/docs/features/hook-patterns-pro|Pro Hook Patterns]] — CPT-based pattern injection
