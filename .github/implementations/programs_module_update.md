# Programs Module Update Plan

## Purpose
This file documents the plan, progress, and TODOs for upcoming changes to the programs module. All steps and related files will be tracked here, and each task will be marked as complete when finished.

---

## Initial Scan: Key Files Related to Programs Module

### Admin Views
- app/views/admin/programs/programs.php
- app/views/admin/programs/edit_program.php
- app/views/admin/programs/edit_program_2.0.php
- app/views/admin/programs/view_program.php
- app/views/admin/programs/reopen_program.php
- app/views/admin/programs/delete_program.php
- app/views/admin/programs/bulk_assign_initiatives.php
- app/views/admin/programs/assign_programs.php
- app/views/admin/programs/unsubmit.php
- app/views/admin/programs/resubmit.php
- app/views/admin/programs/manage_programs.php

### Agency Views
- app/views/agency/programs/program_details.php
- app/views/agency/programs/view_other_agency_programs.php
- app/views/agency/programs/edit_program.php
- app/views/agency/programs/create_program.php
- app/views/agency/programs/view_submissions.php
- app/views/agency/programs/delete_program.php
- app/views/agency/programs/edit_submission.php
- app/views/agency/programs/add_submission.php
- app/views/agency/programs/view_programs.php

### API Endpoints
- app/api/programs.php
- app/api/program_user_assignments.php
- app/api/program_submissions.php
- app/api/program_outcome_links.php
- app/api/get_program_targets.php
- app/api/get_period_programs.php

### AJAX Endpoints
- app/ajax/get_program_stats.php
- app/ajax/upload_program_attachment.php
- app/ajax/save_submission.php
- app/ajax/delete_program_attachment.php
- app/ajax/get_target_progress.php
- app/ajax/check_program_number_duplicate.php
- app/ajax/program_numbering.php
- app/ajax/get_program_submission.php
- app/ajax/download_program_attachment.php

### CSS (Pages)
- assets/css/pages/program.css
- assets/css/pages/view-programs.css

### JS (Admin)
- assets/js/admin/programs_list.js
- assets/js/admin/programs_list_fixed.js
- assets/js/admin/programs_admin.js
- assets/js/admin/manage_programs.js
- assets/js/admin/bulk_assign_initiatives.js

### JS (Agency)
- assets/js/agency/view_programs.js
- assets/js/agency/enhanced_program_details.js
- assets/js/agency/program_form.js
- assets/js/agency/program_details.js
- assets/js/agency/create_program.js
- assets/js/agency/add_submission.js
- assets/js/agency/program_management.js
- assets/js/agency/program_submission.js

---

## [Update] Agency-Side Program Status Indicator & Hold Points

### [2024-06-18] Status Options Update
- Statuses now include: "Active", "On Hold", "Completed", "Delayed", "Cancelled" (selectable by program owner/focal).

### Requirements (Finalized)
- Add a program status indicator (distinct from rating) to agency-side program views.
- Only the program owner and focal can set/update the status.
- Statuses include: "Active", "On Hold" (with hold point details), "Completed", "Delayed", "Cancelled" (extensible for future statuses).
- When a program is set to "On Hold":
  - Capture reason (selectable or free text), remarks (optional), created at (timestamp), ended at (when resumed/changed).
  - Each program can have multiple hold points (history of holds).
  - Only the current (ongoing) hold point can be edited; ended hold points are read-only.
- Full status history (including all status changes and hold points) is accessible via a modal on the program details page.

### TODO List (Agency Side)
- [x] 1. Receive user requirements for the new feature or change in the programs module
- [x] 2. Update this plan with specific tasks based on requirements
- [ ] 3. Propose and implement database schema changes for program status and hold points
- [ ] 4. Update backend (API/AJAX) to support status and hold point management (CRUD for current, read-only for ended)
- [ ] 5. Update agency-side program views to display current status and hold point details
- [ ] 6. Add a modal to program details page for full status history (status changes and hold points)
- [ ] 7. Update JS and CSS as needed for new UI elements
- [ ] 8. Test all new/updated functionality
- [ ] 9. Refactor or optimize related code if needed
- [ ] 10. Update documentation and mark tasks as complete

### Progress Log
- Initial scan and documentation created. Awaiting user requirements.
- [2024-06-18] Finalized requirements for agency-side program status indicator, hold points, and status history modal. Ready to proceed with DB and backend design. 

---

## [2024-06-18] Proposed Database Schema Changes

### 1. programs Table (if not already present)
- Add a `status` field (ENUM or VARCHAR) to store the current status: 'active', 'on_hold', 'completed', 'delayed', 'cancelled'.

### 2. program_status_history Table (new)
- `id` (PK, auto-increment)
- `program_id` (FK to programs)
- `status` (ENUM or VARCHAR: 'active', 'on_hold', 'completed', 'delayed', 'cancelled')
- `changed_by` (FK to users)
- `changed_at` (timestamp)
- `remarks` (optional, text)

### 3. program_hold_points Table (new)
- `id` (PK, auto-increment)
- `program_id` (FK to programs)
- `reason` (text or ENUM if you want predefined reasons)
- `remarks` (text, optional)
- `created_at` (timestamp)
- `ended_at` (timestamp, nullable)
- `created_by` (FK to users)

#### Notes:
- When a program is set to 'on_hold', a new row is added to `program_hold_points` (with `ended_at` NULL until resumed).
- All status changes (including hold, resume, complete, etc.) are logged in `program_status_history` for auditability.
- Only the current (ongoing) hold point can be edited; ended hold points are read-only.

---

### Next Steps
- [ ] Review and implement DB schema changes (migration SQL)
- [ ] Update backend logic to use new tables for status and hold point management 

---

## [2024-07-10] Planned API Endpoints & Backend Logic (Agency Side)

### Endpoints to Add/Update
- **GET /api/programs/status**: Get current status and hold point for a program
- **GET /api/programs/status_history**: Get full status history for a program
- **POST /api/programs/set_status**: Set/update status (owner/focal only)
- **POST /api/programs/hold_point**: Create or update (active) hold point (owner/focal only)
- **POST /api/programs/end_hold_point**: End the current hold point (owner/focal only)

### Backend Logic
- Only program owner or focal can change status or manage hold points
- All status changes are logged in program_status_history
- When status is set to 'on_hold', a new row is added to program_hold_points (with ended_at NULL)
- When hold ends, ended_at is set for the current hold point
- Only the current (ongoing) hold point can be edited; ended hold points are read-only

### Next Steps
- [ ] Implement API endpoints and backend logic
- [ ] Update agency-side views, JS, and CSS
- [ ] Test and document 

---

## [2024-07-10] Implementation Complete & Next Steps

### ✅ Completed
- [x] Database schema for status, status history, and hold points
- [x] Backend API endpoints for status/hold point management
- [x] Agency program details view: status indicator, hold point, modals
- [x] JS logic for AJAX, UI, and modals
- [x] CSS for badges, hold info, and modals

### Next Steps: Testing & Documentation
- [ ] Test all status/hold point workflows (change, hold, resume, complete, cancel, delayed)
- [ ] Test permissions (only owner/focal can edit)
- [ ] Test status history and hold point history modal
- [ ] Test UI/UX on desktop and mobile
- [ ] Update documentation/usage notes if needed
- [ ] Remove any test code or unused assets
- [ ] Mark all tasks as complete in this file

### Summary
All core features for the agency-side program status indicator and hold point management are implemented and integrated. Proceed with thorough testing and final documentation before closing the task. 

---

## [2025-07-15] Clean Rewrite Plan for Edit Program Status Management

### Why Rewrite?
- Previous implementation was not robust on the edit program page (UI/JS issues, modal problems, asset loading conflicts).
- Need a clean, minimal, and reliable integration for status/hold point management.

### Clean Rewrite Plan
1. **Remove all previous status/hold point code from `edit_program.php`.**
2. **Rebuild the status indicator, buttons, and modals from scratch, tailored for the edit program page.**
3. **Write a new, minimal JS file just for the edit program page (no legacy or shared code).**
4. **Ensure Bootstrap JS and required CSS are loaded in the correct order.**
5. **UI/UX:**
   - Status badge in the card header (left-aligned).
   - “Change Status” and “Status History” buttons in the card header (right-aligned).
   - Modals for status editing and history, with unique IDs and reliable open/close behavior.
6. **Test all workflows (status change, hold, resume, cancel, etc.) on the edit program page.**
7. **Document the new implementation and update this file as tasks are completed.**

### TODO
- [ ] Remove all previous status/hold point code from `edit_program.php`
- [ ] Add new status badge and buttons to card header
- [ ] Add new, minimal modals for status editing and history
- [ ] Write new JS for status/hold point management (edit page only)
- [ ] Ensure Bootstrap JS and CSS are loaded
- [ ] Test all workflows and UI/UX
- [ ] Update documentation and mark tasks as complete 