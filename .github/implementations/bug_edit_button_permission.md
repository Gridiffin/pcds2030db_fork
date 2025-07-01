# Bug: Unauthorized Edit Redirect in View Programs

## Problem

- As a normal user, clicking the "Edit" button on a program owned by another user redirected back to the view programs page.
- This was due to the UI previously hiding the edit button for non-owned programs, but backend logic already allows editing by any agency user.

## Solution Plan

- [x] Identify the authorization logic in `update_program.php` that causes the redirect.
- [x] Update the UI in `view_programs.php` to always show the "Edit" button for all programs, regardless of ownership.
- [x] Optionally, show a tooltip or disabled button for non-owned programs to clarify permissions. (Not needed, as all users can now edit all programs.)
- [x] Test to ensure normal users can access the edit page for other users' programs, and the UI reflects correct permissions.
- [x] Mark this issue as complete when resolved.

## Notes

- The UI now always shows the "Edit" button for all programs.
- The backend already allows normal users to edit any program.
- No further changes are needed unless business rules change.
- Issue resolved and implementation complete.
