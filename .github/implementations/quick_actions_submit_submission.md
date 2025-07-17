# Quick Actions: Change 'View Submission' to 'Submit Submission'

## Problem

Currently, the Quick Actions section in `program_details.php` shows a "View Submission" button. The requirement is to change this to a "Submit Submission" button, which opens a modal allowing the user to select a submission to submit. Only the names/labels should change, not the underlying functionality.

## Solution Plan

- [x] 1. **Update Quick Actions Button**

  - Change the "View Submission" button to "Submit Submission" in the Quick Actions card.
  - The button should open the same modal as before.

- [x] 2. **Update Modal Content**

  - The modal title and button labels are changed to "Submit Submission" and "Submit".
  - The modal still lists all submissions (not just drafts).
  - No change to the underlying functionality.

- [x] 3. **Submission Action**

  - No change. The button still links to the view page for the submission.

- [x] 4. **UI/UX Improvements**

  - Modal and buttons are styled consistently with the rest of the app.

- [x] 5. **Documentation**
  - All steps are complete. Only names/labels were changed, not the functionality.
