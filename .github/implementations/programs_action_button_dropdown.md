# Enhancement: Programs Table Action Button Dropdown

## Problem
- The current action button layout is cluttered and not optimal for both desktop and mobile.
- User wants View and Delete as main buttons, with a dropdown for Edit Submission and Edit Program.

## Requirements
- In the Actions column:
  - Show View and Delete as main buttons.
  - Add a dropdown (three dots or caret) containing:
    - Edit Submission (links to add_submission.php or edit_submission.php as appropriate)
    - Edit Program (links to edit_program.php)
- Ensure the dropdown is accessible and works well on mobile (touch-friendly, full-width if needed).

## Plan
- [ ] 1. Refactor the Actions column in view_programs.php:
    - [ ] a. Show View and Delete as main buttons.
    - [ ] b. Add a Bootstrap dropdown for Edit Submission and Edit Program.
- [ ] 2. Ensure the dropdown is accessible and touch-friendly for mobile users.
- [ ] 3. Test on both desktop and mobile breakpoints.
- [ ] 4. Update documentation and mark tasks as complete. 
- [x] Fix button text visibility on white backgrounds when active/focused. (Added CSS override to view-programs.css to force dark text color when background is white) 
- [x] Centralize all table button styles in assets/css/components/buttons.css for .btn, .btn-outline-secondary, and .dropdown-toggle. Removed local overrides from view-programs.css. All table action buttons now follow a unified, maintainable style. 
- [x] Fix Edit Submission link to use program_id and period_id (not submission_id) for correct submission lookup in edit_submission.php. 
- [x] Unify Edit Submission page with Add Submission page: now provides a full, modern, actionable edit experience with all relevant fields, attachments, and context. 