# Fix Admin Navigation Bar Redirections

## Problem Analysis

The admin navigation bar has incorrect URL paths in the links. Currently, the links are pointing to:
```
/views/admin/[file].php
```

But the correct path should be:
```
/app/views/admin/[file].php
```

This is causing navigation issues as the links are not redirecting to the correct file locations.

## Solution Steps

- [x] Examine the admin navigation bar file (`admin_nav.php`) to identify all incorrect paths
- [x] Update all occurrences of `/views/admin/` to `/app/views/admin/` for proper redirection
- [x] Verify that all links in the navigation bar use the correct APP_URL prefix
- [x] Check if any other navigation files have similar issues

## Implementation

The fix involves updating all link href attributes in the admin navigation bar file to use the correct path structure.

### Changes Made:
- [x] Updated navbar brand link: `APP_URL . '/views/admin/dashboard.php'` → `APP_URL . '/app/views/admin/dashboard.php'`
- [x] Updated dashboard link: `APP_URL . '/views/admin/dashboard.php'` → `APP_URL . '/app/views/admin/dashboard.php'`
- [x] Updated Programs dropdown links:
  - `APP_URL . '/views/admin/programs.php'` → `APP_URL . '/app/views/admin/programs.php'`
  - `APP_URL . '/views/admin/assign_programs.php'` → `APP_URL . '/app/views/admin/assign_programs.php'`
- [x] Updated Users dropdown links:
  - `APP_URL . '/views/admin/manage_users.php'` → `APP_URL . '/app/views/admin/manage_users.php'`
  - `APP_URL . '/views/admin/add_user.php'` → `APP_URL . '/app/views/admin/add_user.php'`
- [x] Updated Outcomes link: `APP_URL . '/views/admin/manage_metrics.php'` → `APP_URL . '/app/views/admin/manage_metrics.php'`
- [x] Updated Reports link: `APP_URL . '/views/admin/generate_reports.php'` → `APP_URL . '/app/views/admin/generate_reports.php'`
- [x] Updated Settings dropdown links:
  - `APP_URL . '/views/admin/system_settings.php'` → `APP_URL . '/app/views/admin/system_settings.php'`
  - `APP_URL . '/views/admin/reporting_periods.php'` → `APP_URL . '/app/views/admin/reporting_periods.php'`
  - `APP_URL . '/views/admin/audit_log.php'` → `APP_URL . '/app/views/admin/audit_log.php'`
