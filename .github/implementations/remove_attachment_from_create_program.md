# Remove Attachment Functionality from Create Program

## Problem
The Create Program wizard currently allows users to upload attachments. The requirement is to remove all attachment-related functionality, including UI, JS, backend handling, and review step references.

---

## Steps

- [x] 1. Remove the Attachments step from the wizard UI (step indicator, navigation, and step content) in `app/views/agency/programs/create_program.php`.
- [x] 2. Remove all JavaScript related to attachment upload, validation, and review from the same file.
- [x] 3. Remove any PHP/backend logic in this file that references attachments.
- [x] 4. Update the review step to not mention or display attachments.
- [x] 5. Update the wizard navigation and progress bar to reflect the new number of steps.
- [ ] 6. Check for and update any CSS/JS imports that are now unused due to this removal.
- [ ] 7. Check for any backend handler files (upload, delete, download) that are now unused and suggest their removal if not used elsewhere.
- [ ] 8. Test the Create Program flow to ensure it works without attachments and the UI is consistent.

---

## Notes
- Ensure all references to attachments are removed for a clean UI/UX.
- If any handler files are used elsewhere, do not delete them, but note their usage.
- Mark each step as complete as you finish it. 