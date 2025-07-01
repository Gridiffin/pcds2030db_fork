# Restrict Program Deletion to Owners Only

## Problem

Currently, any user can delete any program listed in the view, even if they are not the owner. This is a security and data integrity issue.

## Solution Steps

- [x] Identify the program owner for each program row (using `owner_agency_id` or similar field).
- [x] Compare the program's owner with the current logged-in user (`$_SESSION['user_id']`).
- [ ] Only render the "Delete" button if the logged-in user is the owner of the program.
- [ ] Update both the draft and finalized programs tables accordingly.
- [ ] Test to ensure users can only delete their own programs, but can view all.
- [ ] Mark this implementation as complete after verification.

## Notes

- This change will be made in `app/views/agency/programs/view_programs.php`.
- No database changes are required.
- This will improve security and user experience.
