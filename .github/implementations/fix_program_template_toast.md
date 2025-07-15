# Fix Incorrect 'Program Template' Toast Display in Program Details

## Problem
The 'Program Template' and 'No Targets' toasts are redundant and clutter the UI. Only the empty state in the main content area is needed for onboarding. Both toasts should be removed from the program details view.

## Steps to Fix

- [x] 1. **Trace the logic** that determines when the 'Program Template' toast is shown in the program details view.
- [x] 2. **Identify how the code checks** if a program is a template (e.g., flag, submission count, etc.).
  - The toast is shown when `$showNoSubmissionsAlert` is true, which is set when `$has_submissions` is false. `$has_submissions` is set based on whether `$latest_submission` is empty. If this variable is not set correctly, the toast will show incorrectly.
- [x] 3. **Update the logic** so the toast only appears if the program has zero submissions.
  - **Root cause:** `get_program_details` did not set `'current_submission'` in the returned array, so `$latest_submission` was always null and `$has_submissions` was always false. The fix was to set `'current_submission'` to the latest submission if available in `get_program_details`.
- [x] 4. **Remove both the 'No Targets' and 'Program Template' toasts** from the program details view. Only the draft alert remains.
- [ ] 5. **Test** with programs that have submissions, no submissions, and no targets, to ensure no onboarding toasts appear.
- [ ] 6. **Update this file** to mark completed steps and summarize the solution. 