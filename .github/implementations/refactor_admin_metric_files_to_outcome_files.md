# Refactor Admin Metric Files to Outcome Files

## Goal
Create outcome-specific versions of admin utility pages (`delete`, `edit`, `view`, `unsubmit`) by refactoring their existing metric counterparts.

## Files to Create/Refactor

- **1. `delete_outcome.php` from `delete_metric.php`**
    - [x] Create `app/views/admin/delete_outcome.php`.
    - [x] Read content of `app/views/admin/delete_metric.php`.
    - [x] Change `metric_id` GET parameter and variable to `outcome_id`.
    - [x] Use `get_outcome_data($outcome_id)` for verification.
    - [x] Update any SQL queries to target outcome data (if not handled by `get_outcome_data` or a specific delete function for outcomes).
    - [x] Change user messages from "metric" to "outcome".
    - [x] Update redirects to `manage_outcomes.php`.
    - [x] Ensure the actual delete logic targets outcomes (using `delete_outcome_data` or direct SQL on `sector_outcomes_data`).

- **2. `edit_outcome.php` from `edit_metric.php`**
    - [x] Create `app/views/admin/edit_outcome.php`.
    - [x] Read content of `app/views/admin/edit_metric.php`.
    - [x] Change `metric_id` GET parameter and variable to `outcome_id`.
    - [x] Update `$pageTitle` to "Edit/Create Outcome".
    - [x] Ensure `get_outcome_data($outcome_id)` is used to fetch existing outcome data.
    - [x] Change all internal logic, variables (e.g., `$metric_data` to `$outcome_data`), and text labels from "metric" to "outcome".
    - [x] Ensure form submissions point to an outcome-specific update handler (implicitly `edit_outcome.php` itself, which then calls `update_outcome_data` or `create_outcome_data`).
    - [x] Added period selection and handling.
    - [x] Assumed existence of `update_outcome_data` and `create_outcome_data` functions.
    - [x] Integrated `metric-editor.js` for outcome data structure, assuming it's adaptable.

- **3. `view_outcome.php` from `view_metric.php`**
    - [x] Create `app/views/admin/view_outcome.php`.
    - [x] Read content of `app/views/admin/view_metric.php`.
    - [x] Change `metric_id` GET parameter and variable to `outcome_id`.
    - [x] Use `get_outcome_data($outcome_id)` to fetch outcome data for display.
    - [x] Updated to use `get_outcome_data` and display outcome-specific details.
    - [x] Changed all text labels, titles, and messages from "metric" to "outcome".
    - [x] Added tabs for Table View, Chart View (placeholder), and Raw JSON Data.
    - [x] Assumes `get_outcome_data` returns comprehensive details including sector name, period name, status, etc.

- **4. `unsubmit_outcome.php` (from a potential `unsubmit.php` or `unsubmit_metric.php`)**
    - [ ] Create `app/views/admin/unsubmit_outcome.php`.
    - [x] Determine the source file (`unsubmit.php` was linked in `manage_metrics.php`). If `unsubmit.php` exists, use it as a base.
    - [ ] Change `metric_id` GET parameter and variable to `outcome_id`.
    - [ ] Implement logic to verify the outcome's existence.
    - [ ] Implement logic to mark the outcome as unsubmitted (e.g., update a status flag, potentially by calling a function like `unsubmit_outcome_data($outcome_id, $conn)`).
    - [ ] Change user messages from "metric" to "outcome".
    - [ ] Update redirects to `manage_outcomes.php`.

- **5. `update_outcome.php` from `update_metric.php` (Admin version)**
    - [x] This file might not be strictly necessary if `edit_outcome.php` handles the update logic directly by calling helper functions like `update_outcome_data` and `create_outcome_data`. The current `edit_outcome.php` is structured this way.
    - [ ] If a separate `update_outcome.php` is still desired for form action, it would primarily take POST data and call these helper functions.

## General Considerations
- Ensure all required files (configs, libraries, etc.) are correctly included.
- Verify that any helper functions (like `is_admin()`, `get_all_sectors()`, `get_outcome_data`, `create_outcome_data`, `update_outcome_data`, `delete_outcome_data`, `unsubmit_outcome_data`, `get_sector_details`, `get_all_reporting_periods`, `get_current_reporting_period`) are available and used correctly.
- Update any session messages to refer to "outcomes".
- The `metric-editor.js` might need adjustments to perfectly fit the concept of "outcomes" if its internal workings are very metric-specific.
