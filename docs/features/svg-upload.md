# SVG Upload

The Aegis plugin provides secure SVG file upload support with configurable sanitization. SVG upload settings are separate from the **SVG block variation** toggle on **Aegis → Blocks**.

## Configuration

Navigate to **Aegis → Settings** (`admin.php?page=aegis-general-settings`).

| Setting | Default | Description |
|---------|---------|-------------|
| SVG Upload | Disabled | Allow SVG files in the media library. |
| Strip Colors | Disabled | Remove color attributes on upload. |
| Strip Dimensions | Disabled | Remove width/height from SVG root. |
| Strip Styles | Disabled | Remove inline style attributes. |

## Security

Uploaded SVGs are sanitized with the `enshrined/svg-sanitize` library:

- Removes `<script>` elements and event handler attributes.
- Removes `<foreignObject>` and dangerous external references.
- Strips data URIs that could contain embedded scripts.

## SVG Gating (Pro)

When **Aegis Pro** is active, [[../../aegis-pro/docs/features/svg-gating|SVG Gating]] disables uploads if SVG upload is turned off in Settings.

## SVG Block Variation

The **SVG** block variation is an Image block (`core/image` + `is-style-svg`) for pasted inline SVG. The style is registered once in PHP. Extras are at **Aegis → Blocks → SVG** (Paste Markup, Mask Mode, Onclick, Inline SVG in text, Inline SVG files). Paste markup in **SVG Settings**; there is no Optimize SVG control. Those toggles do not control Media Library SVG uploads, and this is not the WordPress **Icon** block (`core/icon`).

For library icons (Core + Aegis collections), use the Icon block — see [Theme SVG Icons](../../themes/aegis/docs/features/svg-icons.md).

## Next Steps

- [[../../themes/aegis/docs/features/svg-icons|SVG Icons]] — Theme icon system
- [[general-settings]] — Other general settings
- [[../../aegis-pro/docs/features/svg-gating|SVG Gating (Pro)]]
