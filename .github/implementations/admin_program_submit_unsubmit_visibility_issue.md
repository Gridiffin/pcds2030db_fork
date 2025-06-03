# Issue: Unsubmit/Resubmit Buttons Not Visible in Admin Program List

## Problem Statement
- The codebase contains logic for displaying Unsubmit/Resubmit buttons in the admin program list (`app/views/admin/programs/programs.php`), but these buttons are not visible in the UI.

## Investigation & Solution Plan

- [x] 1. Confirm code for buttons exists in `programs.php` (already done)
- [x] 2. Check the data structure for each `$program` in the table:
    - Is `submission_id` set?
    - Is `is_draft` set and correct?
    - Is `status` set and correct?
- [x] 3. Check the output of `get_admin_programs_list()`:
    - Does it include `submission_id`, `is_draft`, and `status` for each program?
    - Are these fields populated for the current period?
- [ ] 4. Check if the current period has any program submissions (draft or submitted)
- [ ] 5. Check if filters (status, sector, agency) are hiding all programs with submissions
- [ ] 6. Check for any JavaScript or CSS that could be hiding the buttons
- [ ] 7. Review the logic for displaying the buttons in the table (conditional checks)
- [ ] 8. Suggest improvements for code clarity and maintainability
- [ ] 9. Implement a fix so that the buttons always appear when appropriate
- [ ] 10. Test thoroughly and document the solution

## Findings So Far
- The SQL in `get_admin_programs_list` uses `JSON_EXTRACT(ps.content_json, '$.status') as status`, but the `status` is also a direct column in `program_submissions`.
- If there is no submission for the current period, or if the program was created outside the current period's date range, the program will not appear in the list and the buttons will not show.
- The logic for displaying the buttons depends on the presence of `submission_id`, `is_draft`, and `status` fields for the current period.

## Next Steps
- Check if the current period has any program submissions (draft or submitted) and if the date filtering is too restrictive.
- Suggest improvements to make the logic more robust and ensure the buttons appear when appropriate.
