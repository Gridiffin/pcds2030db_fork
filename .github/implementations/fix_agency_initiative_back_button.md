# Fix: Agency Initiative Details - Back Button Navigation

## Problem

The "Back to Initiatives" button on the agency initiative details view (`app/views/agency/initiatives/view_initiative.php`) currently navigates to `view_initiatives.php`, but the user expects it to go to `initiatives.php` (the main initiatives listing page).

## Solution Plan & TODOs

- [x] Document the issue and solution steps in this file
- [x] Locate the "Back to Initiatives" button in the code and check its URL
- [x] Update the button’s URL to point to the correct page (`initiatives.php`)
- [x] Check for other references to `initiative.php` in this file and related navigation
- [ ] Test and mark the task as complete in this file _(in progress)_

---

**Notes:**

- User clarified that the back button should go to `initiatives.php`, not `view_initiatives.php`.
- The navigation bar is not the problem.
- The button URL has been updated to `initiatives.php`.
