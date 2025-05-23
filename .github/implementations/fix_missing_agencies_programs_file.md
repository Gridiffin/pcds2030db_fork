# Missing agencies/programs.php File Fix

## Issue Description
The admin page `edit_program.php` is encountering a fatal error due to a missing file:
```
Warning: require_once(D:\laragon\www\pcds2030_dashboard\app/lib/agencies/programs.php): Failed to open stream: No such file or directory
```

## Root Cause
During the project restructuring, the `agencies/programs.php` file appears to have been moved to the `deprecated` folder, but the references to this file in the active codebase were not updated. The file needs to be recreated in the new structure at `app/lib/agencies/programs.php`.

## Implementation Plan

1. Create the `app/lib/agencies/programs.php` directory and file based on the deprecated version
2. Update any references if necessary
3. Test the fix by accessing edit_program.php

## Implementation Details

The file was recreated by:
1. Copying the original code from `deprecated/includes/agencies/programs.php`
2. Updating the require paths to match the new structure
3. Adding fallback code to check for dependencies:
   ```php
   // Check if the core.php file exists in the agencies directory, if not we need to adapt
   if (file_exists(dirname(__FILE__) . '/core.php')) {
       require_once 'core.php';
   } else {
       // Include necessary functions from other places if core.php doesn't exist
       require_once dirname(__DIR__) . '/session.php';
       require_once dirname(__DIR__) . '/functions.php';
   }
   ```
4. Testing both `edit_program.php` and `submit_program_data.php` to ensure they load without errors

## Tasks
- [x] Create the agencies directory in app/lib if it doesn't exist
- [x] Copy and adapt the agencies/programs.php file from deprecated/includes/agencies/programs.php
- [x] Update any paths in the new file to match the new structure
- [x] Test edit_program.php to ensure it loads without errors

## Implementation Date
May 22, 2025
