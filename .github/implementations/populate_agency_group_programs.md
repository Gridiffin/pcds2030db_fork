# Problem: Populate `agency_group` in `programs` Table

## Goal

Map each `programs.owner_agency_id` to its correct `agency_group` by referencing the `users` table, and update the `programs.agency_group` column accordingly.

---

## Step-by-Step Solution

- [x] Confirm the mapping logic: `programs.owner_agency_id` → `users.user_id` → `users.agency_group_id`
- [x] Write a parameterized SQL update query to set `programs.agency_group` from `users.agency_group_id`
- [ ] Test the query on a backup/sample
- [ ] Verify that all `agency_group` values are now correct
- [ ] Document the process and clean up any test files

---

## Notes

- All queries must be parameterized to prevent SQL injection.
- This operation should be run with care, ideally on a backup first.
- If any `owner_agency_id` does not exist in `users`, its `agency_group` will remain `NULL`.
- If you want to automate this in PHP, use prepared statements and proper error handling.
