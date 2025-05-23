# Fix ROOT_PATH constant error

The error occurs because the `ROOT_PATH` constant is undefined in the admin and agency dashboard files. This causes a fatal error when trying to access these pages after login.

## Problem Analysis

- Error: `Undefined constant "ROOT_PATH"` in both:
  - `D:\laragon\www\pcds2030_dashboard\app\views\admin\dashboard.php`
  - `D:\laragon\www\pcds2030_dashboard\app\views\agency\dashboard.php`
  
- The `ROOT_PATH` constant is used to reference files from the root of the project.
- Based on the `index.php`, there's already a constant called `PROJECT_ROOT_PATH` being defined, but not `ROOT_PATH`.
- Need to ensure `ROOT_PATH` is defined before it's used in both dashboard files.

## Solution Steps

- [x] Check if `ROOT_PATH` is defined in any of the included files in `admin/dashboard.php` and `agency/dashboard.php`
- [x] Identify the best approach to fix this:
   - Option 1: Define `PROJECT_ROOT_PATH` in both dashboard files (CHOSEN)
   - Option 2: Define `ROOT_PATH` in a common file that's included by both dashboards
   - Option 3: Modify dashboard files to use `PROJECT_ROOT_PATH` instead of `ROOT_PATH`
- [x] Implement the chosen solution by adding the `PROJECT_ROOT_PATH` definition to both dashboard files
- [x] Identified potential issues with other files in the app/views directory
- [ ] Test login for both admin and agency users
- [ ] Verify no other errors occur after the fix
- [ ] Consider implementing a long-term solution to prevent similar issues in other files

## Implementation

### Immediate Fix
1. Added `PROJECT_ROOT_PATH` definition to both dashboard files:
   - `app/views/admin/dashboard.php`
   - `app/views/agency/dashboard.php`

### Long-term Solution Recommendation
There are many PHP files in both the admin and agency directories that might face the same issue. For better maintainability, consider implementing one of these solutions:

1. Create a bootstrap.php file that defines all necessary constants and is included at the beginning of any directly accessed PHP file:
```php
<?php
// bootstrap.php
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
```

2. Create a common_init.php file in app/views/admin and app/views/agency directories:
```php
<?php
// common_init.php for views
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
```

3. Implement a front controller pattern where all requests pass through index.php, which then routes to the appropriate PHP file.

The first two options are quicker to implement, while the third is a more substantial architectural change but would provide better long-term maintainability.
