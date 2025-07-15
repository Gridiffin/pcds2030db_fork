# Feature: Add "View Submission" Button to Program Details

## Description
Add a prominently placed "View Submission" button to the program details page for agency users. The button should:
- Be visible only to users with permission to view submissions for the program.
- Link to the correct submission view for the current program.
- Match the UI style and be placed in a logical, visible location (e.g., quick actions or header).
- As of the latest update, clicking the button opens a modal to select a submission by reporting period.

---

## TODO List

- [x] 1. Analyze `app/views/agency/programs/program_details.php` for best button placement (header, quick actions, etc.)
- [x] 2. Identify the correct submission view page and required parameters (e.g., program_id, submission_id)
- [x] 3. Implement the button in the UI, using centralized button classes for styling
- [x] 4. Add logic to show the button only to users with permission to view submissions
- [x] 5. Test the button to ensure it links to the correct submission view and respects permissions
- [x] 6. Replace the 'View Submission History' button in the quick actions section with the new 'View Submission' button
- [x] 7. Remove the 'Targets & Progress' section from the program details page
- [x] 8. Update this file to mark completed steps and document any improvements or issues found
- [x] 9. Change the 'View Submission' button to trigger a modal that lists all submissions by reporting period, each with a 'View' button

---

## Implementation Details
- The 'View Submission' button now triggers a modal instead of a direct link.
- The modal lists all available submissions for the program, grouped by reporting period, with a 'View' button for each.
- Selecting a submission in the modal redirects to the correct detailed view page.
- The modal uses Bootstrap/modal styles for consistency.
- All quick action buttons use the `btn-outline-success` class for a white background and green text/border.
- A subtitle is present below the 'View Submission' button for clarity.
- Visibility is controlled by the existing `can_view_program($program_id)` logic.

---

## Notes & Suggestions
- Ensure the button is added to main.css if new styles are needed, and follow the established button style.
- If the submission view page is missing or needs improvement, document and suggest changes.
- Consider adding a tooltip or description for the button if space allows. 