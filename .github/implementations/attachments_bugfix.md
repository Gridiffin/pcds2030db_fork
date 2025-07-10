# Attachments Bugfix Implementation Plan

## Problem
- Uploading a single file results in duplicate records in the database.
- Deleting a file only removes it from the UI, not from the database or filesystem unless the backend is notified.
- Old files persist after deletion and re-upload, causing the list to grow.
- Attachment filenames are missing in the UI, showing as blank or 'Not set'.

## Solution
- Prevent duplicate uploads by checking for existing files before inserting.
- Ensure frontend always calls the backend to delete attachments and refreshes the list after deletion.
- On save, process any pending deletions.
- Ensure backend sends the correct filename property and frontend renders it properly.

## Implementation Steps
- [x] 1. Document the problem and solution in this file.
- [x] 2. Audit and fix backend: ensure filename is set and sent in attachment data.
- [x] 3. Audit and fix frontend: ensure correct property is used for filename in rendering logic.
- [x] 4. Prevent duplicate uploads in backend.
- [x] 5. Add frontend fallback for missing filenames (show 'Unnamed file').
- [x] 6. Ensure frontend always calls backend for deletions and refreshes list after deletion.
- [ ] 7. After upload/save, refresh the attachment list from the backend and show a loading indicator or success message.
- [ ] 8. Test upload, delete, and re-upload flows for correctness.
- [ ] 9. Mark this checklist as complete and remove this file after successful testing. 