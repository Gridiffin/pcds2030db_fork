# Split Period Selector Component

## Goal

Split the period selector into two components:

- **Dashboard Period Selector**: For dashboard views, does not use `program_id` and only changes the period context.
- **Editing Period Selector**: For editing a program, uses `program_id` and fetches program data for the selected period.

## Tasks

- [x] Analyze current `period_selector.php` and identify dashboard vs. editing logic.
- [ ] Create `period_selector_dashboard.php` for dashboard use (no `program_id` logic).
- [ ] Create `period_selector_edit.php` for editing use (handles `program_id` and AJAX fetch).
- [ ] Refactor code for maintainability and avoid duplication (extract shared PHP/HTML as needed).
- [ ] Update documentation/comments for clarity.
- [ ] Mark this implementation as complete.

## Notes

- Ensure both components follow project coding standards and are well-documented.
- Use shared code where possible to avoid duplication.
- Test both components in their respective contexts.
