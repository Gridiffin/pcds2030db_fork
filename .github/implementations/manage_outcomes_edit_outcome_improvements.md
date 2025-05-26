# Remove Chart View Tab from Manage Outcomes & Show Editable Table in Edit Outcome

## Problem
1. The Manage Outcomes page currently includes a Chart View tab, which should be removed so only the Table View remains.
2. The Edit Outcome page only displays columns for editing, but it should show a full editable table (all rows and columns, each cell editable) based on the outcome's data.

## Solution Steps

### 1. Remove Chart View Tab (manage_outcomes.php)
- [x] Remove the Chart View tab from the tab navigation.
- [x] Remove the Chart View tab content section.
- [x] Remove any related Chart.js or chart-specific JS code if not used elsewhere.

### 2. Show Editable Table in Edit Outcome (edit_outcome.php)
- [x] Update the outcome editor section to render a full table (rows and columns) from `data_json`.
- [x] Make each cell an editable input (text/number as appropriate).
- [x] Ensure the table is populated with real data, not just columns.
- [x] Update the save logic to collect all cell values and save them back to `data_json`.
- [x] Ensure the UI is user-friendly and consistent with project styles.
- [ ] Display a second header row for units (under each year) in the editable table.
- [ ] Make each unit cell editable.
- [ ] Update save logic to store units for each year in the `units` object in `data_json`.

## Database Structure Analysis (2025-05-26)
- The `sector_outcomes_data` table contains a `data_json` field.
- `data_json` structure:
  - `columns`: array of years (e.g., ["2022", "2023", ...])
  - `data`: object with months as keys (e.g., "January"), each mapping to an object of year:value pairs.
    - Example: `data["January"]["2022"] = 408531176.77`
- Table for editing should have:
  - Rows: months (January, February, ...)
  - Columns: years (from `columns` array)
  - Each cell: editable, null-safe (handle null/empty values)

## Additional Tasks
- [ ] Update table rendering in edit_outcome.php to match this structure.
- [ ] Update save logic to reconstruct `data_json` in this format.
- [ ] Add null checks to avoid deprecated warnings (e.g., htmlspecialchars(null)).
- [ ] Test with real data from DB.

## Notes
- Test both pages after changes.
- Ensure all changes follow project coding standards and are maintainable.
- Suggest further improvements if code structure can be optimized.

---

**This file will be updated as tasks are completed.**
