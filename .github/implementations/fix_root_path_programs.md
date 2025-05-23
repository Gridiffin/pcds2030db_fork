# ROOT_PATH Fix Implementation - programs.php

## Issue Description
The admin page `programs.php` was encountering a fatal error due to an undefined constant `ROOT_PATH`, similar to issues fixed previously in other files like `generate_reports.php`, `manage_users.php`, `dashboard.php`, and other admin files.

## Implementation
The fix involved:

1. Adding the definition of `PROJECT_ROOT_PATH` at the beginning of the file:
   ```php
   // Define project root path for consistent file references
   if (!defined('PROJECT_ROOT_PATH')) {
       define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
   }
   ```

2. Replacing all instances of `ROOT_PATH` with `PROJECT_ROOT_PATH`:
   - In the include statements at the beginning of the file:
     ```php
     require_once PROJECT_ROOT_PATH . 'app/config/config.php';
     require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
     require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
     require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
     require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
     require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
     require_once PROJECT_ROOT_PATH . 'app/lib/admins/statistics.php';
     ```
   - In the dashboard header include:
     ```php
     require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
     ```
   - In the period selector include:
     ```php
     require_once PROJECT_ROOT_PATH . 'app/lib/period_selector.php';
     ```

## Testing
After the changes were implemented, the page loads correctly without the fatal error about the undefined constant ROOT_PATH.

## Related Issues
This fix is similar to previous fixes made to:
- generate_reports.php
- manage_users.php
- dashboard.php
- assign_programs.php
- reporting_periods.php

## Implementation Date
May 22, 2025
