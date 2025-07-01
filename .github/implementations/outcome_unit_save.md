# Problem: Outcome Table Does Not Save Column Unit

## Description

When creating a new outcome using the flexible table designer, if a unit is set for a column, it is not saved in the outcome's data. Only the column IDs and data are saved, but not the column metadata (such as unit, type, label, etc.).

## Steps to Solve

- [x] Identify where the table data is collected and serialized (collectTableData function in JS).
- [ ] Update the collectTableData function to include the full column definitions (including unit, type, label, etc.) in the output JSON, not just the column IDs.
- [ ] Ensure the backend can handle and store this richer structure (if needed).
- [ ] Test the outcome creation to confirm that the unit is saved and retrievable.
- [ ] Mark this implementation as complete and remove any test files if created.

## Notes

- This change will make the outcome data more descriptive and allow for correct display and processing of units in the future.
- No changes to the database schema are required, as the data is stored as JSON.
