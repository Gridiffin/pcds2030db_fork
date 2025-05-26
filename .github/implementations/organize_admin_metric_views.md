## Plan: Organize Admin Metric View Files

**Objective:** Move all admin-related metric view files into a new dedicated folder `app/views/admin/metrics/` to improve project structure.

**Steps:**

1.  [x] Identify all admin metric view files in `app/views/admin/`.
    *   `delete_metric.php`
    *   `edit_metric.php`
    *   `manage_metrics.php`
    *   `update_metric.php`
    *   `view_metric.php`
2.  [x] Create a new directory: `d:\laragon\www\pcds2030_dashboard\app\views\admin\metrics\`.
3.  [x] Move the identified files from `d:\laragon\www\pcds2030_dashboard\app\views\admin\` to `d:\laragon\www\pcds2030_dashboard\app\views\admin\metrics\`.
4.  [x] Update all references to these moved files throughout the codebase to reflect their new location. This will involve searching for includes/requires of these files.
5.  [x] Mark tasks as complete in this markdown file.
