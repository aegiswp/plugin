# Frequently Asked Questions

Common questions about the Aegis companion plugin.

## General

### Do I need the Aegis theme?

**Yes.** The Aegis plugin requires the Aegis theme and will not load without it. This is a hard gate — the plugin is not compatible with other block themes.

### Do I need Aegis Pro?

No. The free plugin includes Map and Modal blocks, integration toggles, code snippets, conditionals, hooks reference, analytics, and SEO schema delegation. Pro adds advanced video, query, hook pattern CPT, and Pro block features.

### What is the difference between theme, plugin, and Pro?

| Product | Includes |
|---------|----------|
| **Aegis theme** | FSE templates, **~200 generic theme patterns**, style variations, design system, six custom blocks (countdown, slider, slide, toggle, toggle-content, related-posts), `core/video` framework enhancements, WooCommerce **FSE templates** |
| **Aegis Plugin** | Map, Modal, admin dashboard, block toggles, snippets, conditionals, integrations, analytics, `core/video` editor extensions, **gated WooCommerce/TI Wishlist block patterns** |
| **Aegis Pro** | Hook pattern CPT, video/BunnyCDN on `core/video`, query conditions on `core/query`, tabs/tab/image-compare blocks, block extensions, license |

## Configuration

### Where do I enter Google Maps credentials?

**Aegis → Connectors → Google Maps** — browser and server API keys.

### Where do I configure analytics?

**Aegis → Connectors** — one tab per provider (Google Analytics, Tag Manager, Clarity, Plausible, Fathom, Matomo, Meta Pixel). Shared consent settings (**Consent Mode v2**, Require Consent, DNT/GPC, Complianz, local scripts, Debug Mode) are on **Privacy**. Debug Mode (Pro) lists valid providers plus privacy state and bypasses DNT/GPC for that admin. Settings export with the global bundle (`analytics`); **Reset Defaults** on Connectors clears them with other connector credentials.

### Where are SVG upload settings?

**Aegis → Settings** — not under Blocks (Blocks has a separate SVG Image variation toggle). Decorative icons use **Aegis → Blocks → Icon** on the WordPress Icon block (`core/icon`), not an Image Icon variation.

### Where is WP Fusion configured?

Enable WP Fusion or WP Fusion Lite at **Aegis → Integrations → CRM**. **CRM Tags** and **CRM Lists** are separate extras. Either extra also enables **Is logged in (CRM)** on the Pro inspector and **WP Fusion Logged-in (CRM)** in Smart Logic (WP Fusion auto-login, not WordPress **User / Logged-in**). Tag and list pickers use the CRM catalog, not the current user's tags. See [[../features/integrations-dashboard]] and [[../../aegis-pro/docs/features/query-conditions#wp-fusion|Query Conditions — WP Fusion]].

### Where is FluentCRM configured?

Enable it at **Aegis → Integrations → CRM**. The toggle has no extras. With **Aegis Pro**, it gates `core/video` funnel, tag, and list events (logged-in contacts; IDs come from the saved block). Tag and list **conditions** are WP Fusion extras on the same category, not FluentCRM. See [[../features/integrations-dashboard]] and [[../../aegis-pro/docs/features/video-stack#lms-and-fluentcrm|Video Stack — LMS and FluentCRM]].

### Where is WooCommerce configured?

Enable it at **Aegis → Integrations → E-commerce**. **Cart Conditions**, **Customer History**, and **Product Context** extras sit under that toggle (Pro inspector; not Smart Logic). Detection uses `WooCommerce`, `WC()`, or `WC_VERSION`. Turning the parent on loads framework shop/cart/checkout and breadcrumb CSS, wraps classic and Store Breadcrumb delimiters, unregisters WooCommerce plugin patterns unless **WooCommerce Patterns** is on, enables `aegis_before_woocommerce_checkout` / `aegis_after_woocommerce_checkout` and `aegis_before_woocommerce_cart` / `aegis_after_woocommerce_cart`, and loads multi-step checkout assets on the multi-step template. Query Loop product filters also need **Blocks → Query Loop → WooCommerce Integration**. An installed but inactive copy of `woocommerce/woocommerce.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]] and [[../../aegis-pro/docs/features/query-conditions#woocommerce|Query Conditions — WooCommerce]].

### Where is Easy Digital Downloads configured?

Enable it at **Aegis → Integrations → E-commerce**. **Cart Conditions**, **Customer History**, and **Download Context** extras sit under that toggle (Pro inspector; not Smart Logic). Detection uses `Easy_Digital_Downloads`, `EDD()`, or `EDD_VERSION`. Turning the parent on loads framework download/checkout CSS (`plugins/edd/edd.css`) and enables `aegis_before_edd_download` / `aegis_after_edd_download`. Download type is EDD `default` / `bundle` (a leftover saved value of `download` is treated as `default`). An installed but inactive copy of `easy-digital-downloads/easy-digital-downloads.php` or `easy-digital-downloads-pro/easy-digital-downloads.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]] and [[../../aegis-pro/docs/features/query-conditions#easy-digital-downloads|Query Conditions — Easy Digital Downloads]].

### Where is AffiliateWP configured?

Enable it at **Aegis → Integrations → E-commerce**. **Referral Visit**, **Affiliate Account**, and **Earnings** extras sit under that toggle (Pro inspector; not Smart Logic). Detection uses `Affiliate_WP`, `affiliate_wp()`, or `AFFILIATEWP_VERSION`. Turning the parent on loads framework Affiliate Area and login/register form CSS, skips AffiliateWP `affwp-forms` on the frontend (admin screens keep it), and enables `aegis_before_affwp_dashboard` / `aegis_after_affwp_dashboard`. An installed but inactive copy of `affiliate-wp/affiliate-wp.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]] and [[../../aegis-pro/docs/features/query-conditions#affiliatewp|Query Conditions — AffiliateWP]].

### Where is Fluent Forms configured?

Enable it at **Aegis → Integrations → Forms**. **Has Submitted**, **Submission Count**, and **Field Value** extras sit under that toggle (Pro inspector; not Smart Logic), along with the pattern-control extra **Fluent Forms Patterns** (`fluentforms_keep_patterns`). Detection uses `FLUENTFORM`, `FLUENTFORM_VERSION`, or `FluentForm\App\Modules\Form\Form`. Turning the parent on loads framework form styling (`plugins/fluent-forms.css`), disables default public styles (`fluentform_load_default_public`), and enables snippet locations `aegis_before_fluentform` / `aegis_after_fluentform`. An installed but inactive copy of `fluentform/fluentform.php` or `fluentformpro/fluentformpro.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]], [[../../aegis-pro/docs/features/query-conditions#fluent-forms|Query Conditions — Fluent Forms]], and [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]].

### Where is Fluent Booking configured?

Enable it at **Aegis → Integrations → Forms**. **Has Booking**, **Upcoming Count**, **Past Count**, and **Booking Status** extras sit under that toggle (Pro inspector; not Smart Logic), along with the pattern-control extra **Fluent Booking Patterns** (`fluentbooking_keep_patterns`). Detection uses `FLUENT_BOOKING_VERSION` or `FluentBooking\App\App`. Turning the parent on loads framework calendar and booking UI CSS (`plugins/fluentbooking.css`). An installed but inactive copy of `fluent-booking/fluent-booking.php` or `fluent-booking-pro/fluent-booking-pro.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]], [[../../aegis-pro/docs/features/query-conditions#fluent-booking|Query Conditions — Fluent Booking]], and [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]].

### Where is Gravity Forms configured?

Enable it at **Aegis → Integrations → Forms**. Detection uses `GFForms`, `GFAPI`, `GF_MIN_WP_VERSION`, or `gravity_form()`. Turning the parent on loads framework form styling (`plugins/gravity-forms.css`), disables default form theme styles (`gform_disable_form_theme_css`), and enables snippet locations `aegis_before_gform` / `aegis_after_gform`. An installed but inactive copy of `gravityforms/gravityforms.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]] and [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]].

### Where is Ninja Forms configured?

Enable it at **Aegis → Integrations → Forms**. Detection uses `Ninja_Forms`, `function_exists( 'Ninja_Forms' )`, `defined( 'NF_PLUGIN_VERSION' )`, or `defined( 'NF_VERSION' )`. Turning the parent on loads framework form styling (`plugins/ninja-forms.css`), dequeues default and opinionated plugin styles (`nf-display`, `ninja-forms-display`, `ninja-forms-display-opinions`, `nf-display-opinions`, `nf-layout-front-end`), and enables snippet locations `aegis_before_nf_form` / `aegis_after_nf_form`. An installed but inactive copy of `ninja-forms/ninja-forms.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]] and [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]].

### Where is LearnDash configured?

Enable it at **Aegis → Integrations → LMS**. Detection uses `LEARNDASH_VERSION`, `class_exists( 'SFWD_LMS' )`, `defined( 'LEARNDASH_LMS_PLUGIN_DIR' )`, or `function_exists( 'learndash_init' )`. Turning the parent on loads framework course, lesson, topic, quiz, and profile styling (`plugins/learndash.css`), binds Focus Mode logo URL (`learndash_focus_header_logo_url` and `learndash_focus_mode_logo`) to the theme custom logo, maps logo alt text (`learndash_focus_header_logo_alt`) to the site name, and enables snippet locations `aegis_before/after_learndash_course`, `aegis_before/after_learndash_lesson`, `aegis_before/after_learndash_topic`, `aegis_before/after_learndash_quiz`, and `aegis_learndash_focus_header/footer`. An installed but inactive copy of `sfwd-lms/sfwd_lms.php` or `learndash/sfwd_lms.php` shows as **Inactive**, not **Not Installed**. Pattern control extra **LearnDash Patterns** (`learndash_keep_patterns`) unregisters LearnDash plugin patterns from the editor at `init` priority 11 in Pro. See [[../features/integrations-dashboard]], [[../../aegis-pro/docs/features/query-conditions#learndash|Query Conditions — LearnDash]], and [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]].

### Where is Code Block Pro configured?

Enable it at **Aegis → Integrations → Developer**. There are no extras. Detection uses `CBPRouter` or the registered `kevinbatdorf/code-block-pro` block — the plugin does not define `CODE_BLOCK_PRO_VERSION`. When the toggle is on, the theme overlay (`plugins/code-block-pro.css`) loads if `wp-block-kevinbatdorf-code-block-pro` is in the markup. An installed but inactive copy of `code-block-pro/code-block-pro.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]] and [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]].

### Where is Syntax Highlighting Code Block configured?

Enable it at **Aegis → Integrations → Developer**. There are no extras. Detection uses `Syntax_Highlighting_Code_Block\PLUGIN_VERSION` or `boot()` — Weston Ruter’s plugin extends `core/code` and does not register a separate block. When the toggle is on, the theme overlay (`plugins/syntax-highlighting-code-block.css`) loads if `hljs` / `shcb-` markup is in the page, not on an unhighlighted `wp-block-code`. An installed but inactive copy of `syntax-highlighting-code-block/syntax-highlighting-code-block.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]] and [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]].

### Where is Meta Box configured?

Enable it at **Aegis → Integrations → Developer**. The **Meta Box Field** extra sits under that toggle. Featured-image **Meta Box Image Field** is on **Conditionals → Image Source** and also needs this parent. Query Loop pickers need **Blocks → Query Loop → ACF/MetaBox Integration**. Detection is `RWMB_Loader` or `rwmb_meta()` (Meta Box AIO counts). Turning the parent on loads framework form CSS and theme colour palettes on Meta Box colour fields. An installed but inactive copy of `meta-box/meta-box.php` or `meta-box-aio/meta-box-aio.php` shows as **Inactive**, not **Not Installed**. See [[../features/integrations-dashboard]] and [[../../themes/aegis/docs/features/plugin-integrations|Theme Plugin Integrations]].

### Why don’t saved visibility rules apply?

The matching extra must be on. **Aegis → Conditionals** gates core types (Visibility, Accessibility, User, Schedule, and Image Source). **Aegis → Integrations** gates plugin types (WooCommerce, EDD, LMS, Fluent Forms/Booking, WP Fusion tags/lists/CRM logged-in, and ACF/Meta Box *field* visibility). Turning an extra off hides its controls and ignores saved rules, including leftover viewport/accessibility CSS classes. Saving Conditionals also flushes hook-pattern caches.

User Capability rules use a primitive slug such as `edit_posts` (**is** / **is not**). Typed values are normalized (`Edit Posts` → `edit_posts`). Object caps like `edit_post` need a post ID and will not match.

Schedule Date & Time values are naive (no offset). They are interpreted in the IANA schedule timezone when **Timezone** is on, otherwise in the site timezone — not the browser's local zone. Abbreviations such as `EST` and offsets such as `UTC+2` stay in the dropdown labeled invalid and evaluation uses the site timezone. Daily ranges whose end is before start wrap overnight.

**Image Source** extras are **Pro**. They change `core/post-featured-image` (not the Visibility panel). Turning a source off ignores that saved source and leaves the featured image as-is. ACF and Meta Box image-source toggles stay disabled unless that plugin is installed and active **and** the matching **Integrations → Developer** parent is on. To show or hide a block from an ACF or Meta Box *value*, use **Integrations → Developer** (**ACF Field** / **Meta Box Field**), not Image Source.

### How do I translate the plugin?

The plugin catalog is `languages/aegis.pot` (text domain `aegis`). Generate it from the plugin directory with `npm run translate` or `npm run translate:studio`. Do not add plugin strings to the theme POT (`wp-content/themes/aegis/languages/aegis.pot`). Pro uses `aegis-pro.pot`. See [[../development/building-assets#translations]].

## Blocks

### Which blocks require the plugin?

**Map** (`aegis/map`) and **Modal** (`aegis/modal`) are registered by the plugin. The theme registers six custom blocks. Video uses **`core/video`** (enhanced by the theme and plugin editor scripts).

### Can I use the plugin without the theme?

No. The plugin checks for the Aegis theme on load and displays an admin notice if it is missing.

### Can I disable block features?

Yes. Use **Aegis → Blocks** to toggle parent blocks and sub-features.

### Why don't I see WooCommerce patterns in the inserter?

WooCommerce block patterns register from `patterns/woocommerce/` only when **WooCommerce is active** and this plugin is loaded. Install WooCommerce and activate the Aegis plugin. FSE templates stay in the theme; patterns are plugin-owned. See [[../features/plugin-patterns]] and [[../../themes/aegis/docs/troubleshooting/faq|Theme FAQ]].

## Performance

### Where are performance settings?

**Aegis → Performance** — oEmbed, dashicons, heartbeat, embed facades, Query Loop Pro gate, and emoji scripts (Pro).

Slider lazy-load stays at **Aegis → Blocks → Slider**. BunnyCDN is at **Aegis → Connectors → BunnyCDN**.

See [[../features/performance|Performance]] for details. Pro per-block Query Loop options are documented in [[../../aegis-pro/docs/features/query-performance|Query Performance (Pro)]].

### Are performance toggles included in export?

Yes. They are stored in the `blocks` group of the global export bundle — see [[../features/general-settings|General Settings]].

## Data and Privacy

### Is data sent to third parties by default?

No. Analytics, Maps, and external services activate only when you configure credentials and enable them.

### Where are snippets stored?

In `uploads/aegis-snippets/`, outside the global settings export bundle.

## Next Steps

- [[installation]] — Install the plugin
- [[dashboard-overview]] — Admin tour
- [[../features/performance|Performance]] — Site-wide and query toggles
- [[../../themes/aegis/docs/troubleshooting/faq|Theme FAQ]]
