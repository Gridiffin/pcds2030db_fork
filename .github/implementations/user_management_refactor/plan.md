# User Management Refactor Plan

## Overview

This document tracks the plan, reasoning, and progress for refactoring `app/views/admin/users/manage_users.php` to improve maintainability, modularity, and adherence to project best practices.

---

## Goals

- Separate concerns (controller logic, view rendering, AJAX handling)
- Reduce code duplication (user table rendering)
- Modularize repeated HTML (partials)
- Move AJAX handler to a dedicated file
- Clean up and simplify the main view file
- Ensure security and maintainability

---

## Steps & To-Do List

- [x] 1. Create a partial for user table rendering (`_user_table.php`)
- [x] 2. Move AJAX table handler to a new file (`app/ajax/admin_user_tables.php`)
- [x] 3. Refactor `manage_users.php` to use the partial and remove inline AJAX handler
- [x] 4. Update JavaScript to use the new AJAX endpoint
- [x] 5. Test all user management functionality (add, edit, delete, AJAX refresh)
- [x] 6. Document any bugs or issues in `docs/bugs_tracker.md`
- [x] 7. Update this file to reflect progress after each step

---

## Reasoning

### Why Modularize User Table Rendering?

- Reduces code duplication for admin/agency tables
- Easier to update table structure or styling in one place

### Why Move AJAX Handler?

- Keeps view files focused on rendering
- Centralizes AJAX logic for easier maintenance

### Why Clean Up Main View?

- Improves readability and maintainability
- Aligns with best practices in `docs/project_structure_best_practices.md`

---

## Progress Log

- **[x]** Step 1: Partial for user table rendering - _completed_
- **[x]** Step 2: AJAX handler file - _completed_
- **[x]** Step 3: Refactor main view - _completed_
- **[x]** Step 4: Update JS - _completed_
- **[x]** Step 5: Test functionality - _completed_
- **[x]** Step 6: Document bugs - _completed_
- **[x]** Step 7: Update progress - _completed_

---

## Test Plan for User Management

### 1. Add User

- [x] Add a new admin user and verify it appears in the Admin Users table.
- [x] Add a new agency/focal user and verify it appears in the Agency Users table.

### 2. Edit User

- [x] Edit an existing user's details and verify changes are reflected in the table.

### 3. Delete User

- [x] Delete a user and verify they are removed from the table (with AJAX refresh).

### 4. Toggle User Active Status

- [x] Activate/deactivate a user and verify the status updates in the table (with AJAX refresh).

### 5. AJAX Table Refresh

- [x] Trigger a table refresh (e.g., after add/edit/delete/toggle) and verify the tables update without a full page reload.

### 6. Security

- [x] Attempt to access the AJAX endpoint as a non-admin and verify access is denied.

### 7. UI Consistency

- [x] Verify that both tables render correctly and consistently using the partial.

---

## Final Summary

- The user management module has been refactored for maintainability, modularity, and best practices.
- All logic is now separated: AJAX handler, view, and partials.
- Code duplication is eliminated, and the UI is consistent.
- All features have been tested and verified.
- No outstanding bugs remain; if any are found in the future, they will be documented in `docs/bugs_tracker.md`.

**Task closed.**
