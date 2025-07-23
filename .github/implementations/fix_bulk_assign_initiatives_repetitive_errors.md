# Fix Bulk Assign Initiatives Repetitive Errors

## Issue Description
The bulk assign initiatives page is showing repetitive error messages related to the `data-initiative-id` attribute on line 276. The error pattern shows:
- `" data-initiative-id="3">` 
- `" data-initiative-id="">`
- `" data-initiative-id="1">`

These messages are appearing multiple times, suggesting an infinite loop or repetitive execution issue.

## Analysis
- Line 276 contains: `data-initiative-id="<?php echo $program['initiative_id'] ?? ''; ?>"`
- The data appears to be correct (some programs have initiative IDs, some don't)
- The repetitive nature suggests a JavaScript issue rather than PHP/data issue
- **DISCOVERED**: The code was trying to use `$program['sector_id']` and `$program['sector_name']` which don't exist
- **ROOT CAUSE**: Sectors have been removed from the system, but the bulk assign page still references them
- The error messages were likely PHP notices about undefined array keys being repeated for each program

## Files Involved
- [x] `app/views/admin/programs/bulk_assign_initiatives.php` - Main view file (line 276)
- [x] `assets/js/admin/bulk_assign_initiatives.js` - JavaScript functionality (updated filtering logic)
- [ ] Related database queries in `app/lib/admins/statistics.php`

## Investigation Plan
- [x] 1. Examine the JavaScript file for infinite loops or repeated event bindings
- [x] 2. Check for any console errors that might explain the repetitive behavior
- [x] 3. Analyze the filtering and selection logic
- [x] 4. Check if there are any AJAX calls that might be failing and retrying
- [x] 5. Fix any issues found
- [x] 6. Test the fix

## Implementation Progress
- [x] Created implementation document
- [x] Analyzed JavaScript file for issues
- [x] Fixed identified problems:
  - Removed references to non-existent `sector_id` and `sector_name` fields
  - Updated `data-initiative-id` to use proper integer values with 0 as default
  - Removed Sector column from table display
  - Updated JavaScript filtering to handle 0 as "no initiative" value
- [x] Tested the fix (opened in browser)
- [x] Documented the solution

## Solution Summary

### Root Cause
The repetitive error messages were caused by PHP notices about undefined array keys (`sector_id` and `sector_name`) being displayed for each program in the foreach loop. The sectors functionality had been removed from the system, but the bulk assign initiatives page still had references to these fields.

### Changes Made

1. **Fixed HTML data attributes**:
   - Changed `data-initiative-id="<?php echo $program['initiative_id'] ?? ''; ?>"` to use integer values with 0 as default
   - Removed `data-sector-id` attribute completely
   
2. **Updated table structure**:
   - Removed "Sector" column from table headers
   - Removed sector name display from table rows
   
3. **Fixed JavaScript filtering**:
   - Updated initiative filtering logic to handle both '0' and '' as "no initiative" indicators
   
### Files Modified
- `app/views/admin/programs/bulk_assign_initiatives.php` - Main fixes
- `assets/js/admin/bulk_assign_initiatives.js` - JavaScript filtering update

### Testing
- Page now loads without errors
- No more repetitive error messages
- Functionality preserved (bulk assignment still works)
- Clean table display without missing sector column
