# Fix Program Draft Validation Error

## Problem
The system throws an error "Program name and description are required" when trying to save a program as a draft without providing a description. This is because the backend function `create_agency_program_draft` requires both fields, while the frontend UI indicates that the description is optional.
Subsequently, after making description optional in the backend for drafts, the error "Program name is required" appears, indicating the program name field is being submitted as empty.

## Proposed Solution
1. Modify the backend validation for creating program drafts to only require the program name, making the description truly optional for drafts. (Completed)
2. Investigate why `program_name` is being submitted as empty when saving a draft and fix the issue.

## Steps
1.  [x] Modify the `create_agency_program_draft` function in `app/lib/agencies/programs.php`.
    *   Change the validation logic to only check for `program_data['name']`.
    *   The description can be empty when saving a draft.
2.  [ ] Test the "Save Draft" functionality to ensure it works correctly with and without a description.
    *   [ ] Identify why `program_name` is empty on draft submission.
    *   [ ] Check client-side JavaScript (`assets/js/agency/program_management.js`) for form handling logic.
    *   [ ] Inspect HTML for form submission buttons in `create_program.php`.
    *   [ ] Clarify the role of the "Scanner" field mentioned by the user.
    *   [ ] Implement necessary fixes (client-side or server-side) to ensure `program_name` is correctly submitted.
