# Newsletter (Search variation)

Newsletter is a **framework** variation of `core/search`, not a custom block. It is registered by `vendor/aegis/framework` and gated at **Aegis → Blocks → Newsletter**.

## Overview

| Property | Value |
|----------|-------|
| Block name | `core/search` |
| Variation name | `newsletter` |
| Trigger | `className` contains `is-style-newsletter` |
| Registered by | Aegis framework (theme vendor) |
| Toggles | Aegis plugin **Blocks → Newsletter** |

The theme does not register a separate `aegis/newsletter` block. Do not confuse this with:

- Theme **Newsletter** patterns (`patterns/newsletter/*`) — Group + Button CTAs, not this variation
- Plugin **Modal Newsletter** (`aegis/modal-newsletter`) — a Modal starter, documented under [[modal]]

When the plugin is inactive, the framework treats Newsletter as enabled.

The `newsletter` style is registered once in PHP (`Newsletter::register_style()`), not via `BlockStyles`. When Newsletter extras are off, `newsletter-editor.js` hides the variation and unregisters the style.

## Feature Toggles

Enable extras at **Aegis → Blocks → Newsletter**. They are off on new installs. Existing sites keep previously implicit-on extras.

Enabling any Newsletter extra also turns the parent variation on (same prefix rule as other Blocks sections). There is no separate parent Newsletter toggle.

### Signup vs decorative fields

A field is a **signup** when it has a submit button (the default inserter variation) or when it is `no-button` and the **saved** placeholder is empty or contains “email”.

Decorative skins (Pro contact hero First name / Last name / Phone) stay `type="text"`, are not required, never show the success message, and do not dispatch `aegis-newsletter-submit`. They still have search `action` stripped so they do not run a site search.

### Free plugin

| Toggle | What it does |
|--------|----------------|
| Email Validation | On signup fields, set `type="email"`, `autocomplete="email"`, and `inputmode="email"`. Off: signup fields stay `type="text"`. Signup fields are always `required`. |
| Success Message | After submit on a signup field, hide the field and show “Thanks for subscribing.” Off, and on decorative fields: no confirmation UI. |
| Custom Placeholder | Honor the Search block’s placeholder on the input. Off: show “Email address”. Signup vs decorative still uses the **saved** placeholder and button. |

Pro does not add Newsletter extras. Aegis Pro’s contact hero pattern reuses `is-style-newsletter` as a visual input skin; keep **Custom Placeholder** on if those fields should keep First name / Last name text.

## Usage

1. Enable the Newsletter extras you need at **Aegis → Blocks → Newsletter**.
2. In the editor, insert **Newsletter** (Search variation) or apply the Newsletter style to a Search block.
3. Set placeholder and button text on the Search block (placeholder is used on the frontend only when Custom Placeholder is on).

The form does not POST to WordPress search. Submit is `preventDefault`. On **signup** fields the theme dispatches `aegis-newsletter-submit` with `{ email }` so a plugin or snippet can send the address to a mailing list.

## Rendering

- PHP `Newsletter.php` (priority 11, after Search) strips `action` / `method` / `role`, removes a leftover search icon, names the input `newsletter`, and applies extras. Signup fields get `data-aegis-newsletter-signup`.
- `newsletter.js` handles submit, native validity, the custom event, and the success state (event and success only on signup fields).
- `newsletter.css` hides the search icon and styles the success message.
- The editor script unregisters the bundled variation (and the Newsletter style) when the parent is off, and re-registers a Subscribe variation when it is on.
- When Newsletter is off, `is-style-newsletter` is stripped so leftover CSS does not hide the search icon; the variation is hidden from the inserter.

## Patterns

- Theme **Newsletter CTA** (`patterns/cta/newsletter.php`) and **Commerce Newsletter** (`patterns/cta/commerce-newsletter.php`) use `is-style-newsletter`.
- Theme **Newsletter** category patterns (banner / inline / split) are button CTAs, not this Search variation.

## Next Steps

- [[block-variations]] — All variation toggles
- [[../../themes/aegis/docs/blocks/block-variations|Theme Block Variations]] — Editor usage
- [[modal]] — Modal Newsletter starter
