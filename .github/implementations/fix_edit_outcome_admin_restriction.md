# Fix: Admins See Restriction Message When Editing Outcomes

## Problem

Admins are seeing the message:

> Creation of new outcomes has been disabled by the administrator...
> when trying to edit outcomes. This message is only intended for outcome creation, not editing. The root cause is that if an invalid or missing outcome ID is provided, or the outcome does not exist, the user is redirected to `manage_outcomes.php`, where the restriction message is shown. This is confusing for admins who expect to edit outcomes.

## Solution Plan

- [x] **Diagnose**: Confirm that the restriction message is only about creation, and that edit links redirect to `manage_outcomes.php` if the outcome is missing/invalid.
- [x] **Improve Error Handling**: Update `edit_outcome.php` and `manage_outcomes.php` to show a clear error message if an outcome is missing or invalid, instead of the creation restriction message. (Implemented in both files)
- [x] **Fix Parameter Mismatch**: The Edit Outcome button in `view_outcome.php` used `?metric_id=`, but `edit_outcome.php` expects `?id=`. The button now uses the correct `?id=` parameter.
- [ ] **Test**: Ensure that admins can always edit existing outcomes, and that clear feedback is given if an outcome is missing.
- [ ] **Document**: Mark each step as complete in this file as progress is made.

## Steps

1. Update `edit_outcome.php` to set a specific error message in the session if the outcome is not found or the ID is invalid (e.g., 'Outcome not found or already deleted.').
2. Update `manage_outcomes.php` to display this error message if present, instead of the creation restriction message.
3. Update `view_outcome.php` so the Edit Outcome button uses `?id=` instead of `?metric_id=`.
4. Test the flow as admin:
   - Editing a valid outcome should work.
   - Editing a missing/invalid outcome should show a clear error, not the creation restriction message.

---

**Progress:**

- [x] Diagnosed the issue and root cause
- [x] Improved error handling in both files
- [x] Fixed parameter mismatch in Edit Outcome button
- [ ] Tested and verified the fix (Reminder: Test as admin to confirm correct behavior)
