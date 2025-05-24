## Fix Edit User Page Error: `agency_group` table missing

**Problem:** The `edit_user.php` page throws a fatal error because it tries to query a table named `agency_group` which does not exist in the database.

**Solution Steps:**

1.  [x] **Identify the missing table:** The error message clearly states `Table 'pcds2030_dashboard.agency_group' doesn't exist`.
2.  [x] **Verify database schema:** Use database tools to confirm the table is indeed missing. (Confirmed via `dbcode_get_tables`)
3.  [ ] **Determine the purpose of `agency_group`:**
    *   The code in `edit_user.php` (around line 202-215) attempts to fetch `id` and `group_name` from `agency_group` to populate a dropdown.
    *   It then tries to match `user['agency_id']` with `group['id']`. This suggests that the `users` table might have an `agency_id` column that was intended to link to this `agency_group` table.
4.  [ ] **Analyze `users` table structure:** Check the `users` table schema for a column named `agency_id` or similar that would store the foreign key to the intended `agency_group`.
5.  [ ] **Decide on a fix:**
    *   **Option A: Create the `agency_group` table.** If this table is essential and was accidentally missed during database setup.
        *   Define columns: `id` (INT, PK, AI), `group_name` (VARCHAR).
        *   Populate with necessary data if any default groups are expected.
    *   **Option B: Remove the `agency_group` functionality.** If this feature was experimental or is no longer needed.
        *   Comment out or remove the code block in `edit_user.php` that queries and displays the agency group dropdown.
        *   Consider if `users.agency_id` (if it exists) needs to be handled or removed.
    *   **Option C: Use an existing table if applicable.** Perhaps there's another table that serves a similar purpose (e.g., `sectors` or if `agency_name` in `users` table was meant to be a direct input rather than a selection). This seems less likely given the specific query.
6.  [ ] **Implement the chosen fix.**
7.  [ ] **Test:** Verify that the "Edit User" page loads correctly and that user editing functionality (especially for agency users) works as expected.

**Initial thought:** The `users` table has an `agency_name` column. It's possible that `agency_group` was an earlier concept and `agency_name` (direct text input) is the current implementation. However, the code explicitly queries for `agency_group` and uses `user['agency_id']`. This needs clarification.

Let's check the `users` table structure first.
