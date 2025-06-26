# Problem: Date Sorting Not Working in Programs Table

## Description

When clicking the 'Last Updated' column header in the programs table, the table does not sort by date as expected. This affects both draft and finalized programs tables in `view_programs.php`.

## Analysis

- The table uses a custom JavaScript sorting script (`table_sorting.js`).
- The date values are rendered as formatted strings (e.g., 'Jun 26, 2025'), which may not be recognized as sortable dates by the script.
- The sorting script may be treating the column as a string, not as a date.

## Solution Plan

- [x] Document the problem and plan.
- [ ] Review and update the sorting logic in `assets/js/utilities/table_sorting.js` to handle date columns.
- [ ] Ensure date cells have a `data-date` attribute with an ISO date value for reliable sorting.
- [ ] Update the table rendering in `view_programs.php` to include this attribute.
- [ ] Test and confirm that sorting works as expected for the date column.
- [ ] Mark this task as complete.

---

## TODO

- [ ] Update JS sorting logic for date columns
- [ ] Add `data-date` attributes to date cells in PHP
- [ ] Test and verify fix
- [ ] Clean up and finalize
