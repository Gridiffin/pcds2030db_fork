## Plan: Organize Admin Program and User View Files

**Objective:** Move all admin-related program and user view files into new dedicated folders `app/views/admin/programs/` and `app/views/admin/users/` respectively, to improve project structure.

**Steps:**

**Programs:**
1.  [x] Identify all admin program view files in `app/views/admin/`.
    *   `manage_programs.php` (moved)
    *   `resubmit.php` (moved)
    *   `unsubmit.php` (moved)
2.  [x] Create a new directory: `d:\laragon\www\pcds2030_dashboard\app\views\admin\programs\`.
3.  [x] Move the identified program files from `d:\laragon\www\pcds2030_dashboard\app\views\admin\` to `d:\laragon\www\pcds2030_dashboard\app\views\admin\programs\`.
4.  [x] Update all references to these moved program files throughout the codebase.

**Users:**
1.  [x] Identify all admin user view files in `app/views/admin/`.
    *   `create_user.php` (moved)
    *   `edit_user.php` (moved)
    *   `manage_users.php` (moved)
2.  [x] Create a new directory: `d:\laragon\www\pcds2030_dashboard\app\views\admin\users\`.
3.  [x] Move the identified user files from `d:\laragon\www\pcds2030_dashboard\app\views\admin\` to `d:\laragon\www\pcds2030_dashboard\app\views\admin\users\`.
4.  [x] Update all references to these moved user files throughout the codebase.

5.  [x] Mark tasks as complete in this markdown file.
