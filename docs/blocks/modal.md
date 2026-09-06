# Modal Block

The **Modal** block (`aegis/modal`) is an accessible popup, off-canvas panel, bottom sheet, or fullscreen overlay.

## Overview

| Property | Value |
|----------|-------|
| Block name | `aegis/modal` |
| Registered by | Aegis companion plugin |
| Requires | Aegis theme + Aegis plugin |

The theme does not register this block. Enable it (or any Modal extra) at **Aegis → Blocks → Modal**. Site-wide instance management is at **Aegis → Modals** — see [[modals]].

## Feature Toggles

Enable extras at **Aegis → Blocks → Modal**. They are off on new installs. Existing sites keep previously implicit-on **free** extras (button, icon, text, image, off-canvas, fullscreen, animations).

Enabling any Modal extra also turns the parent block on (same prefix rule as other Blocks sections). There is no separate parent Modal toggle.

### Free plugin

| Toggle | What it does |
|--------|----------------|
| Button Trigger | Render a button that opens the modal. Off: no button trigger. |
| Icon Trigger | Open from an inline SVG/HTML icon button. |
| Text Trigger | Open from a text-style control. |
| Image Trigger | Open from a selected image. |
| Off-Canvas Modes | Off-canvas (side panel) and bottom-sheet layouts. Off: those types render as a centered popup. |
| Fullscreen Mode | Full-viewport layout. Off: fullscreen blocks render as a popup. |
| Animations | Fade, slide, and zoom. Off: no enter animation. |

Popup layout, close behavior, focus trap, and size controls stay available while the block is on. The dialog surface and close control follow the theme light/dark tokens (`--wp--custom--body--background` / `--wp--custom--body--color`) unless a custom overlay or background color is set. Legacy `#ffffff` / `rgba(0,0,0,0.5)` values are treated as unset so they do not freeze light mode.

**Close Button Position**

| Option | Where the control sits |
|--------|------------------------|
| **Top right of screen** (default) | Overlay corner (`position: fixed`), same as Image lightbox |
| **On dialog** | Top-right of the panel for every layout, including popup and fullscreen |

An icon trigger with no custom HTML uses a default SVG. An image trigger with no selected image falls back to the trigger text button.

### Pro

| Toggle | What it does |
|--------|----------------|
| Exit Intent | Exclusive trigger type **and** a stacking Pro toggle (open when the pointer leaves the top of the viewport). |
| Scroll Depth | Exclusive trigger type **and** a stacking Pro toggle (open after a scroll percentage). |
| Time Delay | Exclusive trigger type **and** a stacking Pro toggle (open after N seconds). |
| Auto Close | Close the dialog after a delay. |
| Show Once | Remember the visitor in `localStorage` for a number of days. |
| Device Visibility | Limit the modal to desktop, tablet, and/or mobile. |

Existing Pro sites keep these extras enabled via a one-time migration (`aegis_modal_pro_toggles_v1`). New installs leave them off. They are not in the generic v3 legacy snapshot (same pattern as Map and Marquee Pro extras).

Pro inspector panels and stacking triggers follow these toggles. Exclusive Pro trigger types in the free inspector follow the same flags, including extra fields that used to live only in the Pro panels (exit-intent sensitivity/delay, scroll percentage and once-per-load).

**Show Once** uses the free `showOnce` attribute (set by the Newsletter and Cookie patterns) and the Pro `showOnceEnabled` toggle. The Pro inspector reads and writes both so turning the panel off actually disables remember-me for those patterns.

## Usage

1. Enable the Modal extras you need at **Aegis → Blocks → Modal**.
2. Insert **Modal** (or start from **Aegis → Modals**).
3. Choose a layout and trigger, then add inner blocks as the dialog body.

Saved attributes are not deleted when an extra is turned off. Render falls back: disabled trigger types use the first enabled click trigger (or none), off-canvas/fullscreen become popup, animations become none, and Pro behaviors are omitted from the frontend data attributes. The editor Select controls show that same fallback without rewriting the saved block.

## Rendering

- PHP `render.php` outputs the trigger (when a click extra is on), the hidden dialog, and `data-*` attributes the view script reads.
- `view.js` opens/closes the dialog, traps focus, clears auto-close timers on close, and honors gated automatic triggers.
- Frontend and editor styles live in `src/Blocks/modal/style.css`.
- Pro stacking triggers (click **plus** exit intent/scroll/time delay) use `aegis-modal-pro-view`. Auto-close, show-once, and device rules reuse the free data attributes. Device hiding uses Pro CSS media-query classes when those extras are on; the free script only applies a one-shot hide when those classes are absent.

## Patterns

Plugin patterns `aegis/modal-video`, `aegis/modal-contact`, `aegis/modal-newsletter`, and `aegis/modal-cookie-consent` register only while the Modal block is enabled.

Creating a starter from **Aegis → Modals** turns on the extras that pattern needs:

| Starter | Always enables | Also enables when Pro is active |
|---------|----------------|----------------------------------|
| Blank / Contact / Video | Button Trigger; Contact and Video also enable Animations | — |
| Newsletter | Animations | Exit Intent, Show Once |
| Cookie consent | Off-Canvas Modes, Animations | Time Delay, Show Once |

## Next Steps

- [[modals]] — Modals admin tab
- [[custom-blocks]] — Plugin custom blocks
- [[../../aegis-pro/docs/features/block-extensions|Pro Block Extensions]]
