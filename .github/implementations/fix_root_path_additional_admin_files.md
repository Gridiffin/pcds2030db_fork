# Fix ROOT_PATH Undefined Constant in Additional Admin Files

## Problem
The constant "ROOT_PATH" is undefined in the following admin files:
- app/views/admin/add_user.php (line 9)
- app/views/admin/edit_user.php (line 9)
- app/views/admin/manage_metrics.php (line 9)
- app/views/admin/view_metric.php (line 9)
- app/views/admin/unsubmit.php (line 9)
- app/views/admin/edit_metric.php (line 9)
- app/views/admin/delete_metric.php (line 8)
- app/views/admin/system_settings.php (line 9)
- app/views/admin/audit_log.php (line 8)

This is causing fatal errors when trying to access these pages.

## Solution
- [✓] Identify all files requiring the fix (add_user.php, edit_user.php, manage_metrics.php, view_metric.php, unsubmit.php, edit_metric.php, delete_metric.php, system_settings.php, audit_log.php)
- [✓] Add a PROJECT_ROOT_PATH definition to each file before any require statements
- [✓] Update all references from ROOT_PATH to PROJECT_ROOT_PATH
- [✓] Test each file to ensure errors are resolved

## Implementation Details
For each file, add the following code block after the initial PHP comment block and before any require statements:

```php
// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
```

Then replace all instances of `ROOT_PATH` with `PROJECT_ROOT_PATH`.

This ensures consistency with the fixes applied to other admin files and maintains compatibility with the project structure.
