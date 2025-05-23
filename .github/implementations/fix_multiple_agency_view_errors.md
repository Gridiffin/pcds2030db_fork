# Fix Multiple Agency View Errors

## Problem Summary
Multiple errors found in agency view files:

1. **Missing function error in submit_metrics.php (line 33):**
   - `Call to undefined function get_agency_sector_metrics()`

2. **Missing function error in view_all_sectors.php (line 56):**
   - `Call to undefined function get_all_sectors_programs()`

3. **ROOT_PATH errors in agency view files:**
   - update_program.php (line 9)
   - program_details.php (line 9)

4. **404 Error when accessing:**
   - http://localhost/pcds2030_dashboard/app/views/$ViewType/delete_program.php

## Analysis Steps
- [x] Identify the files with errors
- [ ] Check where the missing functions should be defined
- [ ] Fix the missing functions issue
- [ ] Fix the ROOT_PATH constant errors
- [ ] Identify and fix the 404 error with delete_program.php

## Solution Plan
1. Find where `get_agency_sector_metrics()` and `get_all_sectors_programs()` functions should be defined
2. Add proper includes to files with missing functions
3. Add PROJECT_ROOT_PATH definitions to the files with ROOT_PATH errors
4. Investigate the delete_program.php 404 error and correct the path/file
