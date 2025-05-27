## Integrate Agency Groups into User Management

**Problem:**
Need to allow administrators to assign an "Agency Group" to users, particularly agency users, via the `edit_user.php` page.

**Assumptions (will need verification):**
*   There is a database table for agency groups (e.g., `agency_groups`).
*   This table has at least an `agency_group_id` (primary key) and `group_name`.
*   The `users` table has a foreign key to link to this table (e.g., `agency_group_id`).

**Solution Steps:**

1.  **Database Confirmation (Manual or via DB Tool when available):**
    *   [ ] Identify the exact name of the agency group table.
    *   [ ] Identify its columns (e.g., `agency_group_id`, `group_name`).
    *   [ ] Confirm how it links to the `users` table (e.g., `users.agency_group_id`).

2.  **Create/Update Helper Function:**
    *   [ ] In `app/lib/functions.php` or `app/lib/admins/users.php`, create or update a function like `get_all_agency_groups()` to fetch all agency groups from the database.
    *   [ ] Ensure the `get_user()` or the query in `edit_user.php` that fetches user details also retrieves the user's current `agency_group_id` and `group_name` (if needed for display before editing).

3.  **Modify `app/views/admin/users/edit_user.php`:**
    *   **Fetch Data:**
        *   [ ] Call `get_all_agency_groups()` to get the list of agency groups.
    *   **Add Form Field:**
        *   [ ] In the HTML form, add a `<select>` dropdown for "Agency Group".
        *   [ ] Populate this dropdown with the fetched agency groups.
        *   [ ] Ensure the user's current agency group is pre-selected.
        *   [ ] This field might be conditionally shown/required based on the user's role (e.g., only for 'agency' users).

4.  **Modify User Update Logic:**
    *   [ ] In the `update_user()` function (likely in `app/lib/admins/users.php` or where user updates are handled):
        *   [ ] Add logic to receive the `agency_group_id` from the `$_POST` data.
        *   [ ] Update the SQL `UPDATE` statement to include setting the `agency_group_id` for the user.
        *   [ ] Add appropriate validation if necessary.

5.  **Modify `app/views/admin/users/manage_users.php` (Optional Display):**
    *   [ ] If desired, update `get_all_users()` to also fetch `agency_group_name` (via a JOIN).
    *   [ ] Add a new column to the user tables to display the agency group.

6.  **Testing:**
    *   [ ] Test editing a user and changing their agency group.
    *   [ ] Verify the change is saved to the database.
    *   [ ] Verify the updated group is displayed correctly (if added to `manage_users.php`).
