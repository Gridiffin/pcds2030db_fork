# Bug: Assigned and Agency Created Programs Show Identical Data

## Problem
- Both "Assigned Programs" and "Agency Created Programs" in the admin dashboard display the same list of programs.
- This is because the backend function `get_admin_programs_list` does not filter by the `is_assigned` field, even though the dashboard view passes an `is_assigned` filter.

## Steps to Fix
- [ ] Update `get_admin_programs_list` in `app/lib/admins/statistics.php` to support filtering by the `is_assigned` column.
- [ ] Add logic to the SQL query to filter programs based on the `is_assigned` value if provided in `$filters`.
- [ ] Test the dashboard to ensure that assigned and agency-created programs are now separated correctly.
- [ ] Review and update any related documentation if necessary.
- [ ] Mark this issue as complete when verified.

## Notes
- This will ensure that only programs with `is_assigned = 1` appear in the "Assigned Programs" list, and only those with `is_assigned = 0` appear in the "Agency Created Programs" list.
- This change will improve data accuracy and user experience in the admin dashboard.
