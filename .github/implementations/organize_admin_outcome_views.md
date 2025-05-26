## Plan: Organize Admin Outcome View Files

**Objective:** Move all admin-related outcome view files into a new dedicated folder `app/views/admin/outcomes/` to improve project structure.

**Steps:**

1.  [x] Identify all admin outcome view files in `app/views/admin/`.
    *   `delete_outcome.php`
    *   `edit_outcome.php`
    *   `manage_outcomes.php`
    *   `unsubmit_outcome.php`
    *   `view_outcome.php`
2.  [x] Create a new directory: `d:\laragon\www\pcds2030_dashboard\app\views\admin\outcomes\`.
3.  [x] Move the identified files from `d:\laragon\www\pcds2030_dashboard\app\views\admin\` to `d:\laragon\www\pcds2030_dashboard\app\views\admin\outcomes\`.
4.  [x] Update all references to these moved files throughout the codebase to reflect their new location. This will involve searching for includes/requires of these files.
5.  [x] Mark tasks as complete in this markdown file.
