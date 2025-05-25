# Refactor manage_metrics.php to use Outcomes

## Goal
Modify `app/views/admin/manage_metrics.php` to display and manage "outcomes" instead of "metrics". This involves changing data sources, labels, and potentially associated logic and scripts.

## Steps

- [ ] 1. **Analyze `manage_metrics.php`**: Understand its current structure, data handling, and UI elements related to metrics.
- [ ] 2. **Analyze `manage_outcomes.php`**: Use this existing page as a reference for how outcomes are handled, including data fetching, display, and actions.
- [ ] 3. **Identify Key Differences**: Note the differences in variable names, data structures, API endpoints, and JavaScript functions between metrics and outcomes.
- [ ] 4. **Plan Code Changes for `manage_metrics.php`**:
    - [ ] Update PHP code to fetch outcome data (e.g., change `$metrics` to `$outcomes`, update any controller/function calls).
    - [ ] Change all UI text labels from "Metric(s)" to "Outcome(s)".
    - [ ] Update links for actions (e.g., Add, Edit, Delete) to point to outcome-related handlers/pages (e.g., `edit_outcome.php` instead of `edit_metric.php`).
    - [ ] Update any form field names if they are metric-specific.
    - [ ] Check for any JavaScript files or functions specific to metrics (e.g., `metric-editor.js`) and see if they need to be replaced or updated (e.g., with `outcome-editor.js`).
- [ ] 5. **Implement Code Changes**: Apply the planned modifications to `app/views/admin/manage_metrics.php`.
- [ ] 6. **Rename file (Optional but Recommended)**: Consider renaming `manage_metrics.php` to a more appropriate name like `manage_outcomes_v2.php` or discuss if its functionality should be merged into the existing `manage_outcomes.php`.
- [ ] 7. **Test Thoroughly**: Verify that the page correctly displays outcomes, and all actions (add, edit, delete, view) work as expected for outcomes.
- [ ] 8. **Update Task List**: Mark completed steps.
