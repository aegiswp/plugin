# Modals Tab

**Aegis → Modals** (`admin.php?page=aegis-modals`) lists Modal blocks on the site and creates draft pages from starters.

Feature flags (triggers, layouts, Pro behaviors) stay at **Aegis → Blocks → Modal**. This page does not duplicate those toggles.

## What You See

- Count of `aegis/modal` blocks in posts, pages, templates, template parts, and synced patterns
- Table of instances: title, host, click-to-copy **ID** and **Shortcode**, trigger, status, **Enable/Disable**, **Edit**, **Delete**
- **Add New** — draft page with a blank Modal block (enables Button Trigger if it was off)
- Pattern starters — Contact, Newsletter, Video, Cookie consent
- Capabilities summary linking to **Blocks → Modal**

Status **Disabled** means the instance toggle is off (the modal is not rendered on the front end). **Trigger off** means the matching Blocks flag is disabled (for example an exit-intent modal while Exit Intent is off). Otherwise status is the host post status (Draft, Published, …).

**Location** shows the host title and type, then:

- **ID** — `modalId` (HTML `id` of the dialog). Click the row to copy.
- **Shortcode** — `[aegis_modal id="…"]`. Click to copy. Place it in any post, page, or shortcode-capable widget to render the same Modal (trigger + dialog) without publishing the host page.

Do not put the shortcode on the same host that already contains the Modal block — that duplicates the dialog `id`.

**Delete** removes that Modal block from the host. If the host is an otherwise empty draft page, the draft is moved to Trash. Enable/Disable does not change the host post status.

## Creating Modals

1. Open **Aegis → Modals**.
2. Click **Add New** or a pattern card. Aegis creates a **draft page** and opens the editor.
3. Edit the Modal block. Either publish the host page / move the block into a template, **or** leave the host as a draft and place `[aegis_modal id="…"]` elsewhere.
4. Enable extra triggers and layouts at **Aegis → Blocks → Modal** if you add them later. Creating a starter already turns on the extras that pattern needs (Animations for contact/video/newsletter/cookie, Off-Canvas for cookie consent, and Exit Intent / Time Delay / Show Once when Pro is active).

Newsletter (exit intent) and Cookie consent (time delay) are Pro triggers. The draft is still created without Pro; enable the matching flag after installing Pro.

## Shortcode

```text
[aegis_modal id="modal-2bb90eea"]
```

The `id` is the Modal block’s `modalId`. The shortcode loads that block from its host (including drafts) and runs the same `render.php` output. Disabled modals and a disabled Modal block produce no front-end markup (admins see a short error). Recursive shortcodes for the same id are ignored.

## Scanning

Instances are cached for one hour and flushed when posts are saved, trashed, or deleted. The scan looks for `<!-- wp:aegis/modal` in post content (limit 500 matching posts).

## Next Steps

- [[modal]] — Modal block details
- [[../../aegis-pro/docs/features/block-extensions|Pro Modal Extensions]]
