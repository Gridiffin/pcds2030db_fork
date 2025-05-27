# Feature: Agency Group User Linking

This document outlines the steps to integrate `agency_group_id` into the user management system. The goal is to allow administrators to assign users with the 'agency' role to an agency group.

## Tasks

-   [ ] **Backend PHP Modifications (`d:\laragon\www\pcds2030_dashboard\app\lib\admins\users.php`):**
    -   [ ] Create `get_all_agency_groups()` function:
        -   Query the `agency_group` table to fetch all agency groups (`id`, `group_name`).
    -   [ ] Modify `add_user()` function:
        -   Include `agency_group_id` in validation if the role is 'agency'.
        -   Add `agency_group_id` to the `INSERT` SQL query and bind parameters.
        -   Set `agency_group_id` to `NULL` if the role is not 'agency'.
    -   [ ] Modify `update_user()` function:
        -   Include `agency_group_id` in validation if the role is 'agency'.
        -   Add `agency_group_id` to the `UPDATE` SQL query and bind parameters.
        -   Ensure `agency_group_id` is set to `NULL` if the role is not 'agency'.
    -   [ ] Modify `get_all_users()` function:
        -   Join with `agency_group` table to fetch `group_name` as `agency_group_name`.
        -   Select `u.agency_group_id`.
-   [ ] **Backend PHP Modifications (User Retrieval for Edit Form):**
    -   [ ] Identify the script/function responsible for fetching a single user's details for the edit form (likely in `d:\laragon\www\pcds2030_dashboard\app\handlers\admin\get_user.php` or within `users.php` itself).
    -   [ ] Modify this script/function to also fetch the user's `agency_group_id`.
-   [ ] **Frontend PHP/HTML Modifications:**
    -   [ ] **`d:\laragon\www\pcds2030_dashboard\app\views\admin\users\add_user.php`:**
        -   Call `get_all_agency_groups()` to get the list of agency groups.
        -   Add a `<select>` dropdown for "Agency Group" (name: `agency_group_id`).
        -   Implement JavaScript to show this dropdown only when "Role" is 'agency', and hide/disable it otherwise.
        -   Ensure the dropdown is cleared or set to a default "None" option if the role is not 'agency'.
    -   [ ] **`d:\laragon\www\pcds2030_dashboard\app\views\admin\users\edit_user.php`:**
        -   Call `get_all_agency_groups()`.
        -   Add a `<select>` dropdown for "Agency Group", pre-selecting the current user's `agency_group_id`.
        -   Implement JavaScript to show this dropdown only when "Role" is 'agency', and hide/disable it otherwise.
        -   Ensure the dropdown is cleared or set to a default "None" option if the role is not 'agency'.
-   [ ] **Update Implementation Plan:** Mark tasks as complete in this file as they are done.
-   [ ] **Testing:**
    -   [ ] Test adding a new user with 'agency' role and assigning an agency group.
    -   [ ] Test adding a new user with a role other than 'agency' (agency group should not be applicable).
    -   [ ] Test editing an existing 'agency' user and changing their agency group.
    -   [ ] Test editing an existing user, changing their role from 'agency' to something else (agency group should be cleared).
    -   [ ] Test editing an existing user, changing their role to 'agency' from something else (agency group dropdown should appear).
    -   [ ] Verify the user list displays the agency group name correctly.
-   [ ] **(Optional) Delete Test Files:** Delete any temporary test files created during development.

## Database Schema
*   **`users` table:** `user_id`, `username`, `password`, `agency_name`, `role`, `sector_id`, `agency_group_id` (INT, NULL), `created_at`, `updated_at`, `is_active`.
*   **`agency_group` table:** `id` (INT, PK), `group_name` (VARCHAR), `sector_id` (INT).

## Key Files for Modification
*   `d:\laragon\www\pcds2030_dashboard\app\lib\admins\users.php`
*   `d:\laragon\www\pcds2030_dashboard\app\views\admin\users\add_user.php`
*   `d:\laragon\www\pcds2030_dashboard\app\views\admin\users\edit_user.php`
*   `d:\laragon\www\pcds2030_dashboard\app\handlers\admin\get_user.php` (or equivalent user fetching logic)
