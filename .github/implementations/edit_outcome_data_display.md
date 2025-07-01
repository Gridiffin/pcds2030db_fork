# Edit Outcome Data Display Issue

## Problem

- When editing an outcome, the data from the JSON (e.g., `{ "columns": ["123"], "data": { "123": { "123": 123 }, "Sample Row": { "123": 123 } } }`) is not displayed in the editable fields.
- The current code expects a different data structure and does not handle the `"data"` key in the JSON.

## Solution Steps

- [x] Identify where the table data is prepared for display.
- [ ] Update the logic to support both legacy and new JSON structures (with a `"data"` key).
- [ ] Ensure the editable fields are populated correctly from the new structure.
- [ ] Test with both legacy and new JSON formats.
- [ ] Mark this task as complete after implementation.

## Notes

- This change will make the outcome editor compatible with both old and new data formats.
- No changes to the frontend are required unless further issues are found after backend update.
