# Fix MySQL GROUP BY Error in Admin Dashboard

## Problem Analysis

The error is a MySQL "Expression not in GROUP BY clause" error that occurs when using MySQL with the `only_full_group_by` SQL mode enabled. This happens when:

1. The query uses GROUP BY but selects columns that are not included in the GROUP BY clause
2. Those columns are not functionally dependent on the GROUP BY columns

Error details:
- Location: `D:\laragon\www\pcds2030_dashboard\app\lib\admins\statistics.php` on line 311
- Function: `get_admin_programs_list`
- Called from: Admin dashboard when getting assigned and agency programs
- Error: `Expression #15 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'ps.status'`

## Solution Steps

- [x] Examine the SQL query in the `get_admin_programs_list` function
  - The function is in `app/lib/admins/statistics.php` around line 311
  - The SQL query selects multiple columns but only groups by `p.program_id`
- [x] Identify the problematic column (ps.status)
  - The error is about `ps.status` not being in the GROUP BY clause
  - There are multiple other selected columns that are also not in the GROUP BY clause
- [x] Fix the GROUP BY clause by adding all selected columns to it
  - Added all selected columns from all tables (p, u, s, ps) to the GROUP BY clause
  - This makes the query compatible with MySQL's ONLY_FULL_GROUP_BY mode
- [ ] Test the fix by loading the admin dashboard

## Implementation

### Changes Made

Updated the GROUP BY clause in the SQL query in `app/lib/admins/statistics.php` to include all selected columns:

```php
// Before
$sql .= " GROUP BY p.program_id ORDER BY p.created_at DESC";

// After
$sql .= " GROUP BY p.program_id, p.program_name, p.description, p.owner_agency_id, p.sector_id, p.is_assigned, p.created_at, p.updated_at, u.agency_name, s.sector_name, ps.status, ps.is_draft, ps.submission_date, ps.updated_at ORDER BY p.created_at DESC";
```

### Explanation

MySQL's `ONLY_FULL_GROUP_BY` SQL mode requires that all selected columns be either:
1. Included in the GROUP BY clause, or
2. Used only within aggregate functions (like SUM, COUNT, MIN, MAX, etc.)

The error occurred because we were selecting columns like `ps.status` but only grouping by `p.program_id`. By adding all selected columns to the GROUP BY clause, we ensure that the query is compatible with MySQL's strict mode.

### Alternative Solutions

Other approaches that could have been used:
1. Apply aggregation functions to non-grouped columns: `MAX(ps.status) AS status`
2. Disable ONLY_FULL_GROUP_BY mode (not recommended as it's a MySQL best practice)
3. Rewrite the query to use subqueries instead of GROUP BY

The chosen solution is the most straightforward and maintains the existing query logic.
