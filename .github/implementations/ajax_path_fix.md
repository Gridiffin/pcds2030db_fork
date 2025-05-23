# AJAX Path Resolution Fix

## Problem
There is an issue with AJAX URL path resolution in several JavaScript files. The code is using an undefined or incorrectly implemented `ajaxUrl()` function, which causes JSON parsing errors when responses are received.

## Solution Steps

- [x] Fix the fetch URL in `assets/js/agency/dashboard.js` to use direct path instead of ajaxUrl function
- [x] Update other AJAX endpoints in `period_selector.js` to ensure correct paths are used
- [x] Fix AJAX path in `admin/reporting_periods.js` for toggle_period_status.php
- [x] Update AJAX call in `admin/programs_list.js` to use direct path

## Implementation Details

The issue occurs because the `ajaxUrl()` function either doesn't exist or is implemented incorrectly. Instead of relying on this function, we're updating the code to use direct relative paths that will work correctly based on the file structure.

For the agency dashboard, we're specifically fixing the fetch call that was causing the JSON parse error by:
1. Using the correct relative path to the AJAX handler: `../ajax/dashboard_data.php`
2. Adding proper response error handling to provide clearer errors
3. Ensuring the response is properly checked before parsing as JSON

## Testing

To verify the fix is working:
1. Load the agency dashboard page
2. Check browser console for any AJAX/fetch errors
3. Verify that dashboard data loads correctly
4. Test period selection functionality
