# Organize Admin Views

This plan outlines the steps to restructure the `app/views/admin/` directory for better organization.

## Tasks

-   [x] Create `app/views/admin/outcomes/` directory.
-   [x] Move `app/views/admin/create_outcome.php` to `app/views/admin/outcomes/create_outcome.php`.
-   [x] Move `app/views/admin/edit_outcome.php` to `app/views/admin/outcomes/edit_outcome.php`.
-   [x] Move `app/views/admin/manage_outcomes.php` to `app/views/admin/outcomes/manage_outcomes.php`.
-   [x] Update all references to the moved outcome files.
-   [x] Create `app/views/admin/metrics/` directory.
-   [x] Move `app/views/admin/create_metric.php` to `app/views/admin/metrics/create_metric.php`.
-   [x] Move `app/views/admin/edit_metric.php` to `app/views/admin/metrics/edit_metric.php`.
-   [x] Move `app/views/admin/manage_metrics.php` to `app/views/admin/metrics/manage_metrics.php`.
-   [x] Update all references to the moved metric files.
-   [x] Create `app/views/admin/programs/` directory.
-   [x] Move `app/views/admin/manage_programs.php` to `app/views/admin/programs/manage_programs.php`.
-   [x] Update all references to the moved program file.
-   [x] Create `app/views/admin/users/` directory.
-   [x] Move `app/views/admin/create_user.php` to `app/views/admin/users/create_user.php`.
-   [x] Move `app/views/admin/edit_user.php` to `app/views/admin/users/edit_user.php`.
-   [x] Move `app/views/admin/manage_users.php` to `app/views/admin/users/manage_users.php`.
-   [x] Update all references to the moved user files.
-   [x] Move `app/views/admin/resubmit.php` to `app/views/admin/programs/resubmit.php`.
-   [x] Move `app/views/admin/unsubmit.php` to `app/views/admin/programs/unsubmit.php`.
-   [x] Update all references to `resubmit.php` and `unsubmit.php`.
-   [x] Create `app/views/admin/audit/` directory.
-   [x] Move `app/views/admin/audit_log.php` to `app/views/admin/audit/audit_log.php`.
-   [x] Update all references to `audit_log.php`.
-   [x] Create `app/views/admin/dashboard/` directory.
-   [x] Move `app/views/admin/dashboard.php` to `app/views/admin/dashboard/dashboard.php`.
-   [x] Move `app/views/admin/dashboard.php.bak` to `app/views/admin/dashboard/dashboard.php.bak`.
-   [x] Update all references to `dashboard.php`.
-   [x] Fix dashboard.php require statements to use proper library files instead of non-existent models.
-   [x] Create `app/views/admin/reports/` directory.
-   [x] Move `app/views/admin/generate_reports.php` to `app/views/admin/reports/generate_reports.php`.
-   [x] Update all references to `generate_reports.php`.
-   [x] Create `app/views/admin/periods/` directory.
-   [x] Move `app/views/admin/manage_periods.php` to `app/views/admin/periods/manage_periods.php`.
-   [x] Move `app/views/admin/reporting_periods.php` to `app/views/admin/periods/reporting_periods.php`.
-   [x] Update all references to `manage_periods.php` and `reporting_periods.php`.
-   [x] Create `app/views/admin/style_guide/` directory.
-   [x] Move `app/views/admin/style-guide.php` to `app/views/admin/style_guide/style-guide.php`.
-   [x] Update all references to `style-guide.php`.
-   [x] Create `app/views/admin/settings/` directory.
-   [x] Move `app/views/admin/system_settings.php` to `app/views/admin/settings/system_settings.php`.
-   [x] Update all references to `system_settings.php`.
-   [x] Review all files in `app/views/admin/` and its subdirectories for correct relative paths in `require_once`, `include`, `header` locations, and HTML links/forms.
-   [x] Verify admin navigation links in `app/views/layouts/admin_nav.php`.
