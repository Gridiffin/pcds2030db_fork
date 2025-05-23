# Fix: Path Error in process_user.php

## Issue
The `process_user.php` file was using incorrect paths for including required files, causing fatal errors:
- Failed to open '../config/config.php' - path was incorrect
- Incorrect paths to other included files like db_connect.php, session.php, etc.

## Resolution
Updated the file inclusion paths to use the absolute project root path to ensure correct inclusion regardless of the file's location:

1. Added PROJECT_ROOT_PATH definition for consistent path resolution
2. Updated all include paths to reference the correct locations:
   - From: `../config/config.php` → To: `PROJECT_ROOT_PATH . 'app/config/config.php'`
   - From: `../includes/db_connect.php` → To: `PROJECT_ROOT_PATH . 'app/lib/db_connect.php'`
   - From: `../includes/session.php` → To: `PROJECT_ROOT_PATH . 'app/lib/session.php'`
   - From: `../includes/functions.php` → To: `PROJECT_ROOT_PATH . 'app/lib/functions.php'`
   - From: `../includes/admin_functions.php` → To: `PROJECT_ROOT_PATH . 'app/lib/admin_functions.php'`

## Files Modified
- `d:\laragon\www\pcds2030_dashboard\app\handlers\admin\process_user.php`

## Technical Notes
This change ensures that the process_user.php file can properly include all required dependencies by using the absolute path from the project root, rather than a relative path that was pointing to non-existent directories.
