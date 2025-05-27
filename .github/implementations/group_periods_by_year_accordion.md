# Group Reporting Periods Table by Year with Accordion UI

## Problem
The reporting periods table currently lists all periods in a single table, making it hard to navigate when there are many years. The user wants the table to be separated by year, with each year collapsible/expandable (accordion style).

## Solution Plan
- [ ] 1. Identify the PHP view responsible for rendering the reporting periods table (likely `app/views/admin/periods/reporting_periods.php` or `app/views/admin/settings/reporting_periods.php`).
- [ ] 2. Update the backend logic to group periods by year (if not already grouped).
- [ ] 3. Refactor the frontend table rendering to output an accordion structure:
    - Each year is a collapsible section (accordion group).
    - Each section contains a table of periods for that year.
- [ ] 4. Add or update JavaScript to handle accordion expand/collapse behavior.
- [ ] 5. Ensure accessibility and mobile responsiveness.
- [ ] 6. Test with multiple years of data to confirm correct grouping and UI behavior.
- [ ] 7. Mark tasks as complete in this file.

## Notes
- Use Bootstrap's accordion component if available, or implement a simple custom accordion if not.
- Maintain consistent styling with the rest of the admin UI.
- Remove any test files after implementation is complete.
