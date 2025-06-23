# Fix Missing Agencies in Reports Filter

## Overview
Investigate and fix the issue where some agencies are missing from the agency filter in the reports interface. This might be related to focal agencies or database filtering logic.

## Investigation Tasks

### ✅ 1. Database Analysis
- [x] Check the users table structure and agency types
- [x] Verify focal vs regular agency distinction
- [x] Check if agencies have programs in the selected period
- [x] Examine the query that populates agency filter

### ✅ 2. Code Analysis
- [x] Review the agency filter population logic in reports
- [x] Check if there are any filtering conditions excluding certain agencies
- [x] Verify the SQL query used to fetch agencies for the filter

### 3. Debugging
- [ ] Compare agency list in reports vs other parts of the application
- [ ] Check if the issue is period-specific or global
- [ ] Test with different reporting periods

### ✅ 4. Fix Implementation
- [x] Update query/logic to include all relevant agencies
- [x] Ensure focal agencies are properly included
- [ ] Test the fix with various scenarios

## ✅ Problem Identified
The `get_all_agencies()` function in `app/lib/admins/agencies.php` was only selecting users with `role = 'agency'`, excluding focal agencies (`role = 'focal'`).

## ✅ Solution Applied
Updated the SQL query to include both agency and focal roles:
```sql
SELECT user_id, agency_name FROM users WHERE role IN ('agency', 'focal') AND is_active = 1 ORDER BY agency_name ASC
```

This now returns all 10 agencies instead of just the 4 regular agencies.

## Notes
- Need to check if this is related to the recent program numbering changes
- Verify if the issue affects all periods or specific ones
- Consider if focal agencies need special handling
