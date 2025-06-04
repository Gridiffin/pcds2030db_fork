# Fix Outcome Submission Page: Remove Metrics & Update Redirections

## Problem
- The submit outcome page (submit_outcomes.php) still uses 'metrics' terminology and logic, which is outdated.
- Redirection paths for 'view outcomes', 'edit outcome', and 'submit draft outcome' are incorrect or use old metric-based files.
- The page should use outcome-based logic and correct file references.

## Solution Steps
- [x] Identify all references to 'metric' in submit_outcomes.php and related files.
- [x] Refactor code to use outcome-based logic (remove metric_id, use outcome_id or similar, or use metric_id as unique key if that's the schema).
- [x] Update action buttons/links:
    - [x] 'View' should link to view_outcome.php
    - [x] 'Edit' should link to edit_outcomes.php (with correct outcome id)
    - [x] 'Submit Draft' should link to a submit_draft_outcome.php (create if missing)
    - [x] 'Delete' should link to delete_outcome.php (with correct outcome id)
- [x] Remove or rename any JS/CSS includes that reference metrics.
- [ ] Test all actions for correct redirection and data handling.
- [ ] Suggest improvements if any code is not best practice.
- [ ] Delete any test files after implementation.

---

## Progress
- [x] Problem and solution documented
- [x] Implementation in progress
- [ ] Testing
- [ ] Cleanup
- [ ] Complete
