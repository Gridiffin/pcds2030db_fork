# Fix: Edit Outcome "Save Changes" Button Not Saving

## Problem

- The "Save Changes" button in `edit_outcome.php` does not save changes.
- The button's click event is overridden by JavaScript to call `saveFlexibleOutcome()`.
- If `saveFlexibleOutcome` is missing or does not submit the form, the form is not submitted and changes are not saved.
- After fixing the button, the outcome name saves, but the table content is reset to zero. This means the JS serialization does not match the PHP backend's expected structure for `data_json`.

## Steps to Solve

- [x] Analyze the PHP and JS code for the button and form submission.
- [x] Check if `saveFlexibleOutcome` is defined in the included JS files.
- [x] If missing or incomplete, define or fix `saveFlexibleOutcome` to ensure it submits the form.
- [x] Test that the form submits and changes are saved.
- [x] Clean up any unnecessary JS overrides if not needed.
- [ ] Align the JS serialization of table data with the PHP backend's expected structure for `data_json`.
- [ ] Test again to ensure both the name and table content are saved correctly.
- [ ] Delete any test files after implementation.

## Notes

- Always ensure the form is submitted after any JS processing.
- Use best practices for unobtrusive JS and progressive enhancement.
- Document the fix and mark tasks as complete as you proceed.
- The JS must serialize the table data in the same structure as the backend expects, otherwise the data will be lost or reset.
