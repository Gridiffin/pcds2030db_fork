# Fix Fatal Path Error in delete_program.php

## Problem
- The file `delete_program.php` is causing a fatal error due to an incorrect path when including the config file.
- The error path is: `D:\laragon\www\pcds2030_dashboard\app\app/config/config.php` (note the duplicate `app/`)
- This happens because `PROJECT_ROOT_PATH` is defined as the project root, but the code appends `app/config/config.php` instead of just `config/config.php`.

## Solution Steps
- [x] Analyze the error and confirm the cause (duplicate `app/` in the path)
- [x] Review the current path calculation in `delete_program.php`
- [x] Update the require/include statements to use `PROJECT_ROOT_PATH . 'config/config.php'` and similar for other includes
- [x] Test the fix to ensure the error is resolved
- [x] Mark this implementation as complete

## Notes
- Ensure all includes in this file use the correct path pattern
- Suggest reviewing other admin/programs files for similar issues
