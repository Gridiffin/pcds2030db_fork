# Fix Fatal Errors in Multiple Agency View Files

## Problem
Several agency view files are encountering fatal errors:

1. `view_programs.php` - Line 97: Call to undefined function `get_current_reporting_period()`
2. `submit_metrics.php` - Line 2: Undefined constant "ROOT_PATH"
3. `view_all_sectors.php` - Line 10: Undefined constant "ROOT_PATH"
4. `create_metric_detail.php` - Line 4: Undefined constant "ROOT_PATH"

## Analysis Steps
- [x] Examine the error in view_programs.php (missing function)
- [ ] Find where get_current_reporting_period() is defined in other files
- [ ] Fix the function include for view_programs.php
- [ ] Fix the ROOT_PATH issue in the remaining files using PROJECT_ROOT_PATH as implemented earlier
- [ ] Test the fixes to ensure all errors are resolved

## Solution Plan
1. Find the missing function definition for `get_current_reporting_period()`
   - Search through the codebase to locate the file containing this function
   - Include the appropriate file in view_programs.php
   
2. Fix ROOT_PATH constant issues in multiple files
   - Add PROJECT_ROOT_PATH definition to all affected files
   - Update require statements to use PROJECT_ROOT_PATH instead
   - Ensure consistent path handling across all agency view files
