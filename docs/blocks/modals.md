# Modals Tab

**Aegis → Modals** (`admin.php?page=aegis-modals`) lists Modal blocks on the site and creates draft pages from starters.

Feature flags (triggers, layouts, Pro behaviors) stay at **Aegis → Blocks → Modal**. This page does not duplicate those toggles.

## What You See

- Count of `aegis/modal` blocks in posts, pages, templates, template parts, and synced patterns
- Table of instances: title / click-to-copy ID, host, trigger, status, **Enable/Disable**, **Edit**, **Delete**
- **Add New** — draft page with a blank Modal block (enables Button Trigger if it was off)
- Pattern starters — Contact, Newsletter, Video, Cookie consent
- Capabilities summary linking to **Blocks → Modal**

Status **Disabled** means the instance toggle is off (the modal is not rendered on the front end). **Trigger off** means the matching Blocks flag is disabled (for example an exit-intent modal while Exit Intent is off). Otherwise status is the host post status (Draft, Published, …).

The ID under the title (`modalId`) is the HTML `id` of the dialog. Click it to copy.

**Delete** removes that Modal block from the host. If the host is an otherwise empty draft page, the draft is moved to Trash. Enable/Disable does not change the host post status.

## Creating Modals

1. Open **Aegis → Modals**.
2. Click **Add New** or a pattern card. Aegis creates a **draft page** and opens the editor.
3. Edit the Modal block, then publish the page (or copy the block into a template).
4. Enable extra triggers and layouts at **Aegis → Blocks → Modal** if you add them later. Creating a starter already turns on the extras that pattern needs (Animations for contact/video/newsletter/cookie, Off-Canvas for cookie consent, and Exit Intent / Time Delay / Show Once when Pro is active).

Newsletter (exit intent) and Cookie consent (time delay) are Pro triggers. The draft is still created without Pro; enable the matching flag after installing Pro.

## Scanning

Instances are cached for one hour and flushed when posts are saved, trashed, or deleted. The scan looks for `<!-- wp:aegis/modal` in post content (limit 500 matching posts).

## Next Steps

- [[modal]] — Modal block details
- [[../../aegis-pro/docs/features/block-extensions|Pro Modal Extensions]]
