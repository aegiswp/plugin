# Marquee (Group variation)

Marquee is a **framework** variation of `core/group`, not a custom block. It is registered by `vendor/aegis/framework` and gated at **Aegis → Blocks → Marquee**.

## Overview

| Property | Value |
|----------|-------|
| Block name | `core/group` |
| Variation name | `marquee` |
| Trigger | `layout.orientation === 'marquee'` |
| Registered by | Aegis framework (theme vendor) |
| Toggles | Aegis plugin **Blocks → Marquee** |

The theme does not register a separate `aegis/marquee` block. Do not confuse this with the **Slider** block’s `type: marquee` option (Splide AutoScroll) — that is `aegis/slider`. See [[../../themes/aegis/docs/blocks/slider]].

When the plugin is inactive, the framework treats Marquee as enabled.

## Feature Toggles

Enable extras at **Aegis → Blocks → Marquee**. They are off on new installs. Existing sites keep previously implicit-on extras.

Enabling any Marquee extra also turns the parent variation on (same prefix rule as other Blocks sections).

### Free plugin

| Toggle | What it does |
|--------|----------------|
| Pause on Hover | Pause the CSS animation on hover and focus. Off: animation keeps running. |
| Direction Control | Reverse the scroll (right-to-left). Off: always forwards. |
| Speed Control | Loop duration in seconds (**lower is faster**). Off: 60s mobile / 90s desktop. |
| Repeat Items | How many times inner items are cloned for a seamless loop (0–10). Off: clone twice. |

**Fade Edges** is always available while Marquee is on. It adds the `fade-horizontal` utility mask (not a Blocks toggle). Legacy `fade-edges` classes are rewritten to `fade-horizontal` on render.

### Pro

| Toggle | What it does |
|--------|----------------|
| Responsive Speed | Separate **Desktop** duration from **Mobile**. Desktop applies from **782px** wide (editor canvas and frontend). Off: one **Duration** control; both breakpoints use that value. |

Existing Pro sites keep Responsive Speed enabled via a one-time migration (`aegis_marquee_pro_toggles_v1`). New installs leave it off. It is not in the generic v3 legacy snapshot (same pattern as Map Pro extras).

## Usage

1. Enable Marquee extras you need at **Aegis → Blocks → Marquee**.
2. In the editor, insert **Marquee** (Group variation) or set a Group’s layout orientation to marquee.
3. Add inner blocks (text, logos, or a nested horizontal Group).
4. Use **Marquee Settings** for loop duration, repeats, pause, reverse, and fade edges (controls follow the toggles above).

**Loop duration** is the time for one full cycle, not pixels-per-second. With Responsive Speed on, change **Desktop** on a wide canvas; **Mobile** applies below 782px.

The editor canvas applies CSS custom properties on the block wrapper. The frontend PHP render wraps children in `.is-marquee`, clones them, and sets duration on each `.aegis-marquee-item`.

## Rendering

- Animation is CSS-only (`marquee.css`). There is no Marquee JavaScript on the frontend.
- PHP render moves children into an inner `.is-marquee` track, clones them, and stamps a per-block duration on `.aegis-marquee-item` (so layout CSS cannot keep a default 60s/90s loop).
- `--marquee-speed-mobile` / `--marquee-speed-desktop` (and a `--marquee-speed` alias), `--marquee-direction`, `--marquee-pause`, and `--marquee-gap` live on that track.
- Items use `width: max-content` so duration maps to content width instead of a stretched full-width Group.
- WordPress treats unknown flex orientations as a column; the track is forced to a horizontal scroller.
- `prefers-reduced-motion: reduce` pauses the frontend animation (the editor canvas may still move).
- When the parent Marquee toggle is off, `is-marquee` is stripped so saved blocks render as ordinary Groups (no clone wrapper, variation hidden from the inserter).

## Patterns

- Theme **Feature Banner** (`patterns/cta/banner.php`) — scrolling announcement bar.
- Pro **Feature Icon Boxes** (`icon-boxes-split`) — two logo strips with fade edges.

## Next Steps

- [[block-variations]] — All variation toggles
- [[../../themes/aegis/docs/blocks/block-variations|Theme Block Variations]] — Editor usage
- [[../../aegis-pro/docs/features/block-extensions|Pro Block Extensions]] — Responsive Speed
