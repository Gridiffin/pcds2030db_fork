# Fix 404 Error When Deleting Users from Admin Side

## Problem
When attempting to delete a user from the admin interface, a 404 error occurs with the following URL:
```
http://localhost/pcds2030_dashboard/app/admin/process_user.php?action=delete_user&user_id=28
```

The system is trying to access `process_user.php` in the incorrect path `app/admin/` instead of the correct path `app/handlers/admin/`.

## Solution
- [✓] Locate the source code that generates the delete user URL
- [✓] Update the URL to point to the correct path at `app/handlers/admin/process_user.php`
- [✓] Test the user deletion functionality to ensure it works correctly

## Implementation Details
Found and fixed multiple incorrect references to the process_user.php file:

1. In manage_users.php (Line 92):
   - Changed: `header('Location: ../../admin/process_user.php?action=delete_user&user_id=' . $_POST['user_id']);`
   - To: `header('Location: ../../handlers/admin/process_user.php?action=delete_user&user_id=' . $_POST['user_id']);`

2. In assets/js/admin/users.js (Line 88):
   - Changed: `fetch(`${window.APP_URL}/admin/process_user.php`, {`
   - To: `fetch(`${window.APP_URL}/app/handlers/admin/process_user.php`, {`

3. In assets/js/admin/user_table_manager.js (Line 81):
   - Changed: `fetch(`${window.APP_URL}/admin/process_user.php`, {`
   - To: `fetch(`${window.APP_URL}/app/handlers/admin/process_user.php`, {`

These changes ensure that all references to the process_user.php file use the correct path structure reflecting the current project organization where PHP handler files are located in app/handlers/admin/ rather than app/admin/.
