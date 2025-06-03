# Fix create_outcomes_detail.php Path Issues

## Problem
The file `create_outcomes_detail.php` has the persistent double `app/app/` path issue causing:
```
Warning: require_once(D:\laragon\www\pcds2030_dashboard\app\app/config/config.php): Failed to open stream: No such file or directory
```

## Root Cause
The `PROJECT_ROOT_PATH` is defined as the project root (`D:\laragon\www\pcds2030_dashboard\`), but the require statements are using `PROJECT_ROOT_PATH . 'app/config/config.php'`, which creates the double `app/app/` path.

## Solution Steps
- [x] Identify the issue in the PROJECT_ROOT_PATH definition
- [x] Fix the PROJECT_ROOT_PATH to point to the correct directory
- [x] Test the fix
- [x] Verify all require statements work correctly

## Files Fixed
- ✅ `d:\laragon\www\pcds2030_dashboard\app\views\agency\outcomes\create_outcomes_detail.php` - **FIXED**

## Expected Outcome
- ✅ All require statements should work without the double `app/app/` path issue
- ✅ The page should load without fatal errors

## Technical Fix Applied
Changed line 10 from:
```php
define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
```

To:
```php
define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
```

This correctly sets `PROJECT_ROOT_PATH` to the actual project root (`d:\laragon\www\pcds2030_dashboard\`) instead of `d:\laragon\www\pcds2030_dashboard\app\`, preventing the double `app/app/` path issue.
