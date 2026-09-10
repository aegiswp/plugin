# Hook Patterns

**Aegis → Hooks** (`admin.php?page=aegis-hook-patterns`) lists hook patterns on the site. Creating and rendering those patterns requires **Aegis Pro**.

## What You See

**Patterns** (default tab):

- Full-width table of instances: title, location (click-to-copy hook name), priority, **Always / Conditional**, status, **Enable/Disable**, **Edit**, **Delete**
- **Add pattern** — opens the hook pattern editor (Pro). Without Pro it links to License
- Quiet link to **Code Snippets** for PHP/CSS/HTML

**Locations** (`&tab=locations`): collapsed catalog of attach points; click a name to copy it. Enabling locations for snippets is on **Code Snippets → Settings**.

Status **Disabled** means the instance toggle is off. Otherwise status is the post status (Draft, Published, …).

**Conditions** **Always** means the pattern has no visibility rules. **Conditional** (or a rule count) means `_aegis_conditions` is active; click it to edit rules in the pattern editor. Which condition types are available is toggled at **Aegis → Conditionals** (including **Page Type**, **User Status**, **User Role**, **User Capability**, **Specific Users**, **Schedule**, and Accessibility) and **Aegis → Integrations** (including WP Fusion tag, list, and CRM logged-in when those extras are on). Saved rules for a disabled extra are ignored. Viewport and accessibility classes wrap the pattern output. Smart Logic on the pattern is evaluated on the front end with the same extras gating as posts. User Capability is a typed slug such as `edit_posts` (**is** / **is not**); object caps like `edit_post` will not match. Schedule extras (Date & Time, weekdays, daily range, timezone) use the dedicated extras UI, not Smart Logic. Naive start/end datetimes are interpreted in the IANA schedule timezone when that extra is on, otherwise the site timezone; non-IANA saved zones stay in the dropdown labeled invalid and evaluate as the site timezone. Daily ranges whose end is before start wrap overnight.

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
- Integration-bridged hooks (`aegis_before_woocommerce_checkout`, `aegis_before_affwp_dashboard`, `aegis_before_gform`, bbPress forum/topic pairs, Co-Authors Plus post-author wrap, etc.)

Snippets attach to any enabled location in this catalog.

## Framework-Fired Theme Hooks

The Aegis framework fires these hooks automatically (see [[../../themes/aegis/docs/features/hook-patterns|Theme Hook Patterns]]):

- `aegis_before_{template-part-slug}` / `aegis_after_{template-part-slug}`
- `aegis_before_content` / `aegis_after_content`

## Fluent Forms Integration Hooks

When **Aegis → Integrations → Forms → Fluent Forms** is on, `IntegrationInjector` bridges:

- `aegis_before_fluentform` (`fluentform/before_form_render`, priority 5)
- `aegis_after_fluentform` (`fluentform/after_form_render`, priority 99)

Catalog labels: **Before Fluent Form**, **After Fluent Form**.

## Gravity Forms Integration Hooks

When **Aegis → Integrations → Forms → Gravity Forms** is on, `IntegrationInjector` bridges (priority 99 on `gform_get_form_filter`):

- `aegis_before_gform`
- `aegis_after_gform`

Catalog labels: **Before Gravity Form**, **After Gravity Form**.

## Ninja Forms Integration Hooks

When **Aegis → Integrations → Forms → Ninja Forms** is on, `IntegrationInjector` bridges:

- `aegis_before_nf_form` (`ninja_forms_before_form_display`, priority 5)
- `aegis_after_nf_form` (`ninja_forms_after_form_display`, priority 99)

Both actions receive `$form_id`. Catalog labels: **Before Ninja Form**, **After Ninja Form**.

## bbPress Integration Hooks

When **Aegis → Integrations → Content → bbPress** is on, `IntegrationInjector` bridges:

- `aegis_before_bbpress_forum` / `aegis_after_bbpress_forum` (`bbp_template_before_forums_loop` / `bbp_template_after_forums_loop`)
- `aegis_before_bbpress_topic` / `aegis_after_bbpress_topic` (`bbp_template_before_single_topic` / `bbp_template_after_single_topic`)

Catalog labels: **Before bbPress forum**, **After bbPress forum**, **Before bbPress topic**, **After bbPress topic**.

## Co-Authors Plus Integration Hooks

When **Aegis → Integrations → Content → Co-Authors Plus** is on, `IntegrationInjector` wraps (priority 99):

- `aegis_before_post_author` / `aegis_after_post_author` on `core/post-author` and `co-authors/block`

Catalog labels: **Before post author / Co-Authors block**, **After post author / Co-Authors block**.

## LearnDash Integration Hooks

When **Aegis → Integrations → LMS → LearnDash** is on, `IntegrationInjector` bridges:

- `aegis_before_learndash_course` (`learndash-course-before`, priority 5, `$post_id, $course_id, $user_id`)
- `aegis_after_learndash_course` (`learndash-course-after`, priority 99, `$post_id, $course_id, $user_id`)
- `aegis_before_learndash_lesson` (`learndash-lesson-before`, priority 5, `$post_id, $course_id, $user_id`)
- `aegis_after_learndash_lesson` (`learndash-lesson-after`, priority 99, `$post_id, $course_id, $user_id`)
- `aegis_before_learndash_topic` (`learndash-topic-before`, priority 5, `$post_id, $course_id, $user_id`)
- `aegis_after_learndash_topic` (`learndash-topic-after`, priority 99, `$post_id, $course_id, $user_id`)
- `aegis_before_learndash_quiz` (`learndash-quiz-before`, priority 5, `$quiz_id, $course_id, $user_id`)
- `aegis_after_learndash_quiz` (`learndash-quiz-after`, priority 99, `$quiz_id, $course_id, $user_id`)
- `aegis_learndash_focus_header` (`learndash-focus-template-start`, priority 5, `$course_id`)
- `aegis_learndash_focus_footer` (`learndash-focus-template-end`, priority 99, `$course_id`)

Catalog labels: **Before LearnDash course**, **After LearnDash course**, **Before LearnDash lesson**, **After LearnDash lesson**, **Before LearnDash topic**, **After LearnDash topic**, **Before LearnDash quiz**, **After LearnDash quiz**, **LearnDash Focus Mode header**, **LearnDash Focus Mode footer**.

## LifterLMS Integration Hooks

When **Aegis → Integrations → LMS → LifterLMS** is on, `IntegrationInjector` bridges:

- `aegis_before_llms_course` (`lifterlms_before_main_content`, priority 5, on single courses)
- `aegis_after_llms_course` (`lifterlms_after_main_content`, priority 99, on single courses)
- `aegis_before_llms_lesson` (`lifterlms_before_main_content`, priority 5, on single lessons)
- `aegis_after_llms_lesson` (`lifterlms_after_main_content`, priority 99, on single lessons)

All actions pass `$post_id`. Catalog labels: **Before LifterLMS course**, **After LifterLMS course**, **Before LifterLMS lesson**, **After LifterLMS lesson**.

## Sensei LMS Integration Hooks

When **Aegis → Integrations → LMS → Sensei LMS** is on, `IntegrationInjector` bridges:

- `aegis_before_sensei_course` (`sensei_before_main_content`, priority 5, on single courses)
- `aegis_after_sensei_course` (`sensei_after_main_content`, priority 99, on single courses)
- `aegis_before_sensei_lesson` (`sensei_before_main_content`, priority 5, on single lessons)
- `aegis_after_sensei_lesson` (`sensei_after_main_content`, priority 99, on single lessons)
- `aegis_before_sensei_quiz` (`sensei_before_main_content`, priority 5, on single quizzes)
- `aegis_after_sensei_quiz` (`sensei_after_main_content`, priority 99, on single quizzes)

All actions pass `$post_id`. Catalog labels: **Before Sensei course**, **After Sensei course**, **Before Sensei lesson**, **After Sensei lesson**, **Before Sensei quiz**, **After Sensei quiz**.

## Next Steps

- [[code-snippets]] — Inject snippets at hooks
- [[injection-preview]] — Preview hook positions
- [[../../aegis-pro/docs/features/hook-patterns-pro|Pro Hook Pattern Management]]
