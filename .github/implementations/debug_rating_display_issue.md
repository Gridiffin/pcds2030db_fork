# Debug Rating Display Issue in Recent Updates Table

## Problem Description
- Names and dates display correctly in the recent updates table
- Ratings still show "not started" despite SQL query fixes
- This suggests a field name mismatch or data flow issue

## Investigation Steps

### 1. Check Current Dashboard Data Flow
- [x] Verify how dashboard.php gets the recentUpdates data
- [x] Check if it's using the fixed DashboardController
- [x] Verify field name consistency between SQL and view

### 2. Check SQL Query Field Names
- [x] Verify getRecentUpdates() method field names
- [x] Ensure the field name matches what the view expects
- [x] Check for any aliasing issues

### 3. Debug Data Flow
- [x] Add debug logging to see actual data structure
- [x] Verify the rating field is being populated
- [x] Check if there are multiple data sources

### 4. Fix Implementation
- [x] Identify the root cause
- [x] Apply the appropriate fix
- [x] Test the solution

## Root Cause Analysis
**Field Name Mismatch Discovered:**
- The `getRecentUpdates()` SQL query was aliasing the rating extraction as `status`
- The dashboard view was trying to access it as `rating`
- This caused the fallback `'not-started'` to always be used

**SQL Query Field Names:**
```sql
-- Before (getRecentUpdates):
COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as status

-- After (getRecentUpdates):
COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as rating
```

## Solution
1. **Updated getRecentUpdates() method**: Changed field alias from `status` to `rating`
2. **Updated getStatsData() method**: Changed field alias from `status` to `rating` for consistency
3. **Updated logic references**: Changed `$program['status']` to `$program['rating']` in processing logic
4. **Fixed formatting issues**: Resolved syntax errors from merged comment lines

## Changes Made
- [x] Fixed field alias in `getRecentUpdates()` SQL query
- [x] Fixed field alias in `getStatsData()` SQL query  
- [x] Updated logic in `getStatsData()` to use `rating` field
- [x] Fixed syntax/formatting issues
- [x] Verified no compilation errors

## Status
- ✅ **IMPLEMENTATION COMPLETE**

The field name mismatch has been resolved. Both SQL queries now use `rating` as the field name, which matches what the dashboard view expects.

## Testing Results
- [x] **Test Script Executed**: Verified both SQL queries now return proper rating fields
- [x] **Dashboard Tested**: Confirmed actual dashboard displays correct ratings
- [x] **No Errors**: All syntax checks passed
- [x] **Test Files Cleaned**: Removed test files as per coding standards

## Technical Details

### Files Modified
1. **`app/controllers/DashboardController.php`**
   - Fixed `getRecentUpdates()` method field alias: `status` → `rating`
   - Fixed `getStatsData()` method field alias: `status` → `rating` 
   - Updated logic to reference `$program['rating']` instead of `$program['status']`
   - Fixed formatting/syntax issues with merged comment lines

### Field Mapping
```php
// Dashboard View Expects:
$rating = $program['rating'] ?? 'not-started';

// SQL Queries Now Provide:
COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as rating
```

## Impact
- ✅ **Recent Updates Table**: Now displays actual program ratings from JSON content
- ✅ **Dashboard Stats**: Statistics correctly calculate from real rating data
- ✅ **Chart Data**: Rating distribution reflects actual program status
- ✅ **Data Consistency**: Both stats and recent updates use same field name

## Final Status
- 🎉 **FIELD NAME MISMATCH RESOLVED**
- 🎉 **RATING DISPLAY WORKING CORRECTLY**
- 🎉 **IMPLEMENTATION COMPLETE**

The rating display issue has been fully resolved. Programs will now show their actual ratings (on-track, delayed, completed, etc.) instead of hardcoded "not-started" values.
