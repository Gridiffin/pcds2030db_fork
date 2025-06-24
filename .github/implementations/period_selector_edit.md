# Period Selector for Edit (update_program.php)

## Goal

Enable users to select a reporting period/quarter when editing a program, and display the targets that exist for the selected period.

## Steps

- [x] 1. Add a period selector dropdown to the edit form.
- [x] 2. Populate the dropdown with available periods (e.g., using get_reporting_periods()).
- [x] 3. On period selection, reload the page with the selected period as a query parameter.
- [x] 4. On page load, use the selected period to fetch the correct program submission for that period.
- [x] 5. Parse and display the targets for the selected period (from content_json or legacy fields).
- [x] 6. Ensure form fields reflect the selected period's data, or are empty if no submission exists.
- [ ] 7. (Optional) Use AJAX for smoother UX (future enhancement).
- [ ] 8. Update documentation if needed.

## Notes

- If no submission exists for the selected period, initialize empty/default values for the form.
- Ensure compatibility with both new (content_json) and legacy data structures.
- Follow project coding standards and update main.css if new styles are added.
- Test for both draft and finalized periods.

---

**Mark each step as complete as you implement.**
