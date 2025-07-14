# Enhancement: Submission Button in View Programs

## Problem
- The current 'Submission Information' button in the view programs page only shows submission details.
- User wants a better UX: clicking the button should redirect to the add new submission page, but with an enhanced UI that includes an edit button for existing submissions.

## Requirements
- Replace the 'Submission Information' button in view_programs.php.
- New button should redirect to add_submission.php for the selected program.
- The add_submission.php page should:
  - Show the current submission (if any) in a read-only or summary mode.
  - Provide an 'Edit' button to allow editing the existing submission.
  - If no submission exists, show the add submission form as usual.

## Plan
- [ ] 1. Update view_programs.php to replace the 'Submission Information' button with a new button that links to add_submission.php.
- [ ] 2. Enhance add_submission.php to:
    - [ ] a. Display existing submission details (if any) in a summary/read-only mode.
    - [ ] b. Show an 'Edit' button to enable editing the submission.
    - [ ] c. If no submission exists, show the add submission form.
- [ ] 3. Ensure UI/UX is clear and user-friendly.
- [ ] 4. Test the new flow for both cases (existing and new submissions).
- [ ] 5. Update documentation and mark tasks as complete. 