# Admin — View Submission Details (Documentation)

This document explains every part of the Admin "View Submission Details" page so a junior developer can understand, modify, or extend it. It covers the main controller page, the content partial that renders the details, the JavaScript bundle, and the CSS bundle. It also points to server helpers used to fetch data and where to change behavior and styles.

**Files referenced**
- `app/views/admin/programs/view_submissions.php` — page controller that prepares data and layout variables
- `app/views/admin/programs/partials/admin_view_submissions_content.php` — main content partial rendering submission details
- `app/views/admin/programs/partials/admin_select_view_submission_period_content.php` — period selector partial used when multiple submissions exist
- `app/lib/admins/admin_submission_data.php` — helper building `$submission_data` returned to the view
- `app/lib/rating_helpers.php` — utility functions used for rating text/logic
- `app/views/layouts/base_admin.php` — page layout wrapper used by the page
- `assets/js/admin/programs/admin-view-submissions.js` — JS bundle for the page
- `assets/css/admin/programs/admin-view-submissions.css` — CSS bundle for the page
- `edit_submission.php` — target edit page linked from this view
- `app/ajax/download_program_attachment.php` — attachment download endpoint

---

## 1) What this page does (high level)
- Purpose: allow an admin to view finalized submissions for a program for a specific reporting period. When multiple submissions exist, the page shows a selector; otherwise it shows the details for a chosen period.
- Main capabilities: navigation between periods, view program metadata, view submission description/date/author, list and analyze targets (timeline/progress/status), list attachments and download them, and link to edit the submission.

## 2) Entry point: `app/views/admin/programs/view_submissions.php`
- Responsibilities:
  - Define `PROJECT_ROOT_PATH` and require core files: `config.php`, `db_connect.php`, `session.php`, `functions.php`, `admins/index.php`, `rating_helpers.php`, `admins/admin_submission_data.php`.
  - Authorization check: `is_admin()` — non-admins are redirected to `login.php`.
  - Read and validate query params: `program_id` (required) and `period_id` (optional). Missing `program_id` redirects to `programs.php` with an error message stored in `$_SESSION`.
  - Build view data by calling `get_admin_submission_view_data($program_id, $period_id)` (in `app/lib/admins/admin_submission_data.php`). Expect a returned array with keys: `program`, `agency_info`, `submission`, `targets`, `attachments`, `rating_info`, `all_submissions`.

- Period selection behavior:
  - If `period_id` is not provided and `count($all_submissions) > 1`, the page sets `$contentFile` to the select-period partial and includes `base_admin.php` to render a period selection UI.

- Layout variables used by `base_admin.php`:
  - `$pageTitle` (string) — page title
  - `$cssBundle` and `$jsBundle` (strings) — Vite bundle names included by the layout
  - `$header_config` (array) — header title, subtitle, variant, and action buttons

- Edit button logic:
  - Added to `$header_config['actions']` when `$submission` exists. The edit URL uses the `period_id` from URL if present, otherwise the submission's `period_id`.

## 3) Data helper: `app/lib/admins/admin_submission_data.php`
- Purpose: centralize SQL queries and assemble the array expected by the view. If you need to change which fields are displayed, modify this helper (update SQL and mapping).
- Typical returned structure (keys used by the view):
  - `program` — program row (program_name, program_number, description, rating, start_date, end_date)
  - `agency_info` — agency metadata (agency_name)
  - `submission` — submission metadata (period_display, period_start_date, period_end_date, submitted_at, submitted_by_name, description)
  - `targets` — array of targets with keys: target_number, target_description, start_date, end_date, status_indicator, status_description, remarks
  - `attachments` — attachments with attachment_id, file_name, file_size_formatted, upload_date, uploaded_by_name
  - `rating_info` — optional computed rating details
  - `all_submissions` — list of available submissions for the program (period_id, period_display, submitted_at, submitted_by_name)

When adding or renaming fields, update this helper so that the view receives the expected keys.

## 4) Partial: `app/views/admin/programs/partials/admin_view_submissions_content.php`
This file holds the markup and inline presentation logic. Key sections:

- No-submissions alert
  - Rendered when `!$submission && empty($all_submissions)`.

- Submissions list (left column)
  - Renders when `$all_submissions` is not empty. Uses a Bootstrap `.list-group` with each item linked to `view_submissions.php?program_id=...&period_id=...`.
  - The active item (where selected `period_id` matches) gets the `.active` class.
  - Elements shown: `period_display`, formatted `submitted_at`, `submitted_by_name`, and a `Finalized` badge.

- Submission Details card (main column, shown when `$submission` exists)
  - Header shows period and `Finalized` badge.
  - Body contains:
    - Program / Agency info
    - Submission Date / Submitted By block
    - Submission Description (escaped with `htmlspecialchars()` and `nl2br()`)
    - Program Details: rating badge (color/text via `match()`), program description, program timeline (start/end)
  - Footer contains note and `Edit Submission` button. The edit URL uses `$period_id ?? $submission['period_id']` to prefer URL-preserved period.

- Program Targets & Timeline Analysis
  - Shown when `$targets` is not empty.
  - Header displays counts and period. Uses `array_count_values(array_column($targets, 'status_indicator'))` to create counts for Completed, In Progress, Delayed etc.
  - For each target the partial computes:
    - `DateTime` objects for target start/end and submission period start/end.
    - `timeline_status` ('upcoming'|'active'|'overdue'), progress percentage, days remaining, elapsed days.
    - Status consistency checks (e.g., completed but timeline active) and sets `$status_consistent` and `$consistency_message`.
  - Renders a left column (description, timeline analysis, progress bar) and right column (status report, remarks, analytics) per target.
  - Uses `match()` to map `status_indicator` and `rating` to badge classes and icons.

- Attachments
  - If `$attachments` not empty, a card lists attachments with download buttons linking to `app/ajax/download_program_attachment.php?id=...` (the endpoint must validate permissions and file existence).

### Important patterns and safety
- Use `htmlspecialchars()` to escape text output.
- Use `nl2br()` on multi-line fields.
- Use `date()` or `DateTime::format` for consistent date rendering.
- Keep server-side permission checks in the helpers or controllers — do not rely on client-side checks for security.

## 5) JavaScript: `assets/js/admin/programs/admin-view-submissions.js`
- Responsibilities:
  - Import the CSS bundle so Vite includes styles.
  - Initialize tooltips using Bootstrap's `Tooltip`.
  - Setup submission navigation handlers for elements with `.submission-period-link` and `.back-to-submissions` (these elements are used by the period-selection partial or elsewhere).
  - Provide `showToast()` fallback (uses global `window.showToast` if present, otherwise `alert`).
  - Export `AdminViewSubmissions` to `window` for debugging or manual invocation.

When adding client-side features (AJAX edits, inline comments, confirm dialogs), extend this file and keep interactions unobtrusive (the page should still work without JS).

## 6) CSS: `assets/css/admin/programs/admin-view-submissions.css`
- Responsibilities and styling targets:
  - Imports shared base CSS via `@import '../shared/base.css';`.
  - Styles list-group active state, submission info grid, targets list and items, badges, attachments, responsive rules, progress bars and small UI improvements.

Where to edit styles:
- Preferred: add a class in the partial and update `admin-view-submissions.css`.
- Alternative (temporary): inline style directly in the partial (not recommended).

If you add new CSS files, update `vite.config.js` or `$cssBundle` in `view_submissions.php` to ensure they're included by the layout.

## 7) Common changes and where to make them
- Add a new display field: update `admin_submission_data.php`, then the partial to render it, then add CSS if needed.
- Change badge colors or labels: either update `match()` mappings inside the partial or centralize into `rating_helpers.php` and change mappings there.
- Change edit workflow (open modal instead of navigate): change the edit anchor in the partial, add a modal in layout or partial, and add client-side handlers in `admin-view-submissions.js` to load `edit_submission.php` via AJAX into the modal.

## 8) Troubleshooting tips
- Missing submission data: run the helper function directly (create a small debug script) to inspect returned array keys and values.
- Edit button missing: confirm `$submission` is truthy and `$header_config` is built before `base_admin.php` is included.
- CSS changes not visible: clear cache or rebuild Vite bundles. Layout includes bundles by `$cssBundle` and `$jsBundle` variables from `view_submissions.php`.

## 9) Quick local test steps
1. Start Laragon/Apache + MySQL.
2. Open admin programs list in browser, select a program with finalized submissions.
3. Visit `view_submissions.php?program_id=<id>` and optionally `&period_id=<period_id>` to view details.

## 10) File quick-reference (paths)
- Page: [app/views/admin/programs/view_submissions.php](app/views/admin/programs/view_submissions.php)
- Content partial: [app/views/admin/programs/partials/admin_view_submissions_content.php](app/views/admin/programs/partials/admin_view_submissions_content.php)
- Period select partial: [app/views/admin/programs/partials/admin_select_view_submission_period_content.php](app/views/admin/programs/partials/admin_select_view_submission_period_content.php)
- Data helper: [app/lib/admins/admin_submission_data.php](app/lib/admins/admin_submission_data.php)
- Rating helpers: [app/lib/rating_helpers.php](app/lib/rating_helpers.php)
- Layout: [app/views/layouts/base_admin.php](app/views/layouts/base_admin.php)
- JS bundle: [assets/js/admin/programs/admin-view-submissions.js](assets/js/admin/programs/admin-view-submissions.js)
- CSS bundle: [assets/css/admin/programs/admin-view-submissions.css](assets/css/admin/programs/admin-view-submissions.css)
- Edit page: [edit_submission.php](edit_submission.php)
- Attachment download: [app/ajax/download_program_attachment.php](app/ajax/download_program_attachment.php)

---

If you want, I can also:
- Add a short debug script that calls `get_admin_submission_view_data()` with an example `program_id` to show returned structure.
- Create a checklist PR updating developer docs to reference this page.

End of document.
