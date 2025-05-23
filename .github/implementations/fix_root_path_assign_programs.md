# Fix ROOT_PATH Constant Error in Assign Programs Page

## Problem Analysis

The error occurs because the `ROOT_PATH` constant is undefined in the assign_programs.php file. This causes a fatal error when trying to access this page.

Error details:
- Location: `D:\laragon\www\pcds2030_dashboard\app\views\admin\assign_programs.php` on line 9
- Error: `Undefined constant "ROOT_PATH"`

This is similar to the issue we previously fixed in the admin and agency dashboard files, where `ROOT_PATH` was being used but not properly defined.

## Solution Steps

- [x] Check the assign_programs.php file to see how it's using ROOT_PATH
  - Found that the file is using ROOT_PATH for including necessary PHP files
  - ROOT_PATH is not defined in the file
- [x] Implement the fix by:
  - Adding the PROJECT_ROOT_PATH definition at the beginning of the file
  - Replacing all ROOT_PATH occurrences with PROJECT_ROOT_PATH in the require statements
- [x] Verify that no other similar issues exist in other files
  - Note: This is likely a pattern that exists in other admin files as well
  - A long-term solution would be to implement a common bootstrap file that defines these constants for all files

## Implementation

### Changes Made:

1. Added PROJECT_ROOT_PATH definition to the file:
```php
// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
```

2. Replaced all instances of ROOT_PATH with PROJECT_ROOT_PATH in require statements at the beginning of the file:
```php
// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
```

3. Fixed an additional ROOT_PATH reference later in the file:
```php
// Changed this line:
require_once ROOT_PATH . 'app/lib/dashboard_header.php';

// To:
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
```

### How This Fix Works

By defining PROJECT_ROOT_PATH at the beginning of the file, we ensure a proper reference to the project's root directory. Then, by using this constant in all the require statements, we maintain correct file references.

The config.php file (which is now properly included) has logic that sets ROOT_PATH based on PROJECT_ROOT_PATH, ensuring backward compatibility with code that still uses ROOT_PATH.

### Testing Instructions

1. Click on the "Assign Programs" button from the admin dashboard
2. Verify that the page loads correctly without any errors
3. Check that all functionality on the page works as expected
