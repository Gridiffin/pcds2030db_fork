# Fix: Period Selection Bug in Update Program

## Problem

- When selecting a period, the system says "missing period id or program id".
- This is because either the form or AJAX request does not include both `program_id` and `period_id` when fetching or updating from `program_submissions`.

## Solution Steps

- [x] Analyze how period selection works in `update_program.php`.
- [x] Ensure all form submissions and AJAX requests include both `program_id` and `period_id`.
- [x] Update the form to include hidden fields for both IDs.
- [x] Update any JS that handles period selection to send both IDs.
- [x] Update server-side code to validate both IDs before querying.
- [x] Test by switching periods and submitting the form.
- [x] Mark this file as complete and delete any test files after implementation.

---

## Progress

- [x] Problem analyzed and plan created.
- [x] Implementation complete: period selector and form now always send both IDs, preventing missing ID errors.
- [x] Ready for test and deployment.
