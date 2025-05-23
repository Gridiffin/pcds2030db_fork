# ROOT_PATH Fix Implementation - Multiple Admin Files

## Issue Description
Multiple admin pages are encountering fatal errors due to an undefined constant `ROOT_PATH`, similar to issues fixed previously in other files. The affected files include:

1. view_program.php (line 9)
2. edit_program.php (line 8)
3. delete_program.php (line 9)
4. reopen_program.php (line 10)

## Implementation Plan
For each file, we made the following changes:

1. Added the definition of `PROJECT_ROOT_PATH` at the beginning of the file:
   ```php
   // Define project root path for consistent file references
   if (!defined('PROJECT_ROOT_PATH')) {
       define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
   }
   ```

2. Replaced all instances of `ROOT_PATH` with `PROJECT_ROOT_PATH` in:
   - Include statements at the beginning of the files
   - Dashboard header includes in each file:
     - view_program.php (line 213) 
     - edit_program.php (line 320)
     - delete_program.php (line 123)
     - reopen_program.php (line 134)

## Tasks
- [x] Fix view_program.php
- [x] Fix edit_program.php
- [x] Fix delete_program.php
- [x] Fix reopen_program.php
- [x] Test each file to ensure it loads without errors

## Related Issues
This fix is similar to previous fixes made to:
- generate_reports.php
- manage_users.php
- programs.php
- dashboard.php
- assign_programs.php
- reporting_periods.php

## Implementation Date
May 22, 2025
