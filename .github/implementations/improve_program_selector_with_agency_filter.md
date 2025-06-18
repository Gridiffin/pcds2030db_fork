# Improve Program Selector UI with Agency Filter on Generate Reports Page

## Problem
The current program selector on the generate reports page is convoluted when there are many programs. There is no way to filter programs by agency, making it difficult to select relevant programs for multi-agency slide reports. The user wants to add an agency filter to the UI to improve usability, but still allow multi-agency selection for the report.

## Solution Plan
- [x] 1. Analyze current implementation of program selector and data flow.
- [ ] 2. Add an "Agency" filter (multi-select dropdown) above the program selector on the generate reports page.
- [ ] 3. Update the backend API (`get_period_programs.php`) to accept agency IDs and filter programs accordingly (while still supporting multi-agency selection).
- [ ] 4. Update the frontend JS (`report-generator.js`) to fetch and display programs based on selected agencies, period, and sector.
- [ ] 5. Refactor the program selector UI to group or filter programs by agency, making it less cluttered.
- [ ] 6. Ensure multi-agency selection is preserved for the slide report.
- [ ] 7. Test the new UI/UX for usability and correctness.
- [ ] 8. Update documentation if needed.
- [ ] 9. Delete any test files after implementation is complete.

---

## Notes
- The agency filter should not restrict the ability to select multiple agencies for the report; it is only for display/filtering convenience.
- Use best practices for UI/UX and code maintainability.
- Ensure all new/modified files are properly referenced and styled according to project conventions.
