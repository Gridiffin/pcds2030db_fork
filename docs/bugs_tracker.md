# Bugs Tracker

## [2024-07-15] Fatal error: Unknown column 'users_assigned' in 'where clause' (User Deletion)

- **File:** app/lib/admins/users.php
- **Line:** 421 (delete_user function)
- **Error:**
  - Fatal error: Uncaught mysqli_sql_exception: Unknown column 'users_assigned' in 'where clause'
- **Cause:**
  - The code checked for program ownership using a non-existent 'users_assigned' column in the 'programs' table.
  - The actual schema uses 'created_by' to track program ownership.
- **Fix:**
  - Updated the code to use 'created_by' instead of 'users_assigned' in the SQL query.
  - No database changes required.
- **Status:** Fixed in code, 2024-07-15.
