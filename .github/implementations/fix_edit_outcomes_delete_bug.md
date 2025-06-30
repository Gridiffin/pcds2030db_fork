# Fix: Row/Column Delete Not Working in Outcome Edit Table

## Problem

- Clicking the delete button for a row or column in the outcome editing table does not actually remove the row/column from the UI or the data structure.
- The root cause is that the `rowLabels` array is not updated when rows are added/removed, and the table rendering logic relies on this outdated array.
- After initial fix, the delete functionality still does not work. Possible causes include event handlers not being attached, selector issues, or JavaScript errors.

## Solution Plan

- [x] Remove the global `rowLabels` variable.
- [x] Always use `Object.keys(data)` to get the current list of rows.
- [x] Update all functions (`renderTable`, `removeRow`, `removeColumn`, etc.) to use `Object.keys(data)` instead of `rowLabels`.
- [x] Ensure that after any add/remove/edit operation, the table is re-rendered from the current state of `data` and `columns`.
- [x] Test that deleting a row or column immediately updates the UI and the underlying data structure.
- [ ] Add debugging output to delete button event handlers to confirm they are firing.
- [ ] Ensure `attachEventHandlers()` is called after every table render.
- [ ] Check for JavaScript errors in the browser console.
- [ ] Mark this task as complete after verifying the fix.

## Notes

- This approach ensures the UI and data are always in sync, and prevents orphaned or undeletable rows/columns.
- No changes to backend PHP are required for this fix.
- Debugging output will help confirm if the event handlers are working as expected.
