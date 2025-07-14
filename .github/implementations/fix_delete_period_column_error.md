# Fix Delete Period Error - Column 'id' Issue

## Problem
User getting error when deleting reporting periods:
```
Error deleting period: An unexpected error occurred: Unknown column 'id' in 'field list'
```

## Root Cause
The delete functionality is still using the old column name `id` instead of the new migrated column name `period_id`.

## Tasks to Fix
- [x] Find delete period AJAX endpoint/handler
- [x] Check what column names are being used in DELETE queries
- [x] Update any references from `id` to `period_id`
- [x] Test the delete functionality
- [x] Update any frontend JavaScript that sends `id` instead of `period_id`

## ✅ COMPLETED - All Column Reference Issues Fixed!

### Root Cause Identified:
Multiple PHP files were using helper functions like `get_column_name('reporting_periods', 'id')` and `build_select_query()` that were mapping old column names to new ones, but the mapping or the functions weren't working correctly.

### Files Fixed:
1. **`app/ajax/delete_period.php`** - Fixed all SQL queries to use direct column names
2. **`app/lib/admins/periods.php`** - Fixed all references in update, delete, and select operations  
3. **`app/api/report_data.php`** - Fixed period ID references in half-yearly aggregation logic
4. **`app/ajax/toggle_period_status.php`** - Fixed status update queries

### Changes Made:
- Replaced all `get_column_name('reporting_periods', 'id')` with direct `period_id` references
- Replaced all `build_select_query()` calls with direct SQL for reporting_periods table
- Updated all column references to use the new migrated structure:
  - `id` → `period_id` 
  - Direct table name instead of helper functions

### Result:
- ✅ Delete period functionality now works correctly
- ✅ All reporting period operations use correct column names
- ✅ No more "Unknown column 'id'" errors
- ✅ System fully compatible with new database structure

## Priority: HIGH - Blocking admin functionality
