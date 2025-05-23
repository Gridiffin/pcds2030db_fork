# Fix ROOT_PATH Constant Error in Reporting Periods Page

## Problem Analysis

The error occurs because the `ROOT_PATH` constant is undefined in the reporting_periods.php file. This causes a fatal error when trying to access this page.

Error details:
- Location: `D:\laragon\www\pcds2030_dashboard\app\views\admin\reporting_periods.php` on line 9
- Error: `Undefined constant "ROOT_PATH"`

This is similar to the issues we previously fixed in other files (admin dashboard, agency dashboard, and assign_programs).

## Solution Steps

- [x] Check the reporting_periods.php file to see how it's using ROOT_PATH
- [x] Implement the fix by:
  - Added the PROJECT_ROOT_PATH definition at the beginning of the file
  - Replaced ROOT_PATH with PROJECT_ROOT_PATH in require statements
- [x] Checked for other instances of ROOT_PATH in the file and fixed them
- [x] Test to ensure the file loads correctly

## Implementation

1. Added PROJECT_ROOT_PATH definition to the file:

```php
// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
```

2. Replaced all instances of ROOT_PATH with PROJECT_ROOT_PATH in require statements:

```php
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
```

3. Also fixed one more ROOT_PATH reference later in the file:

```php
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
```

## Expected Outcome

The Reporting Periods management page should now load correctly without the undefined constant error. The page is now able to properly include all required files using the PROJECT_ROOT_PATH constant.
