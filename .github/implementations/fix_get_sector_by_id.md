# Fix: Undefined function get_sector_by_id() in edit_outcome.php

## Problem
- Fatal error: Call to undefined function get_sector_by_id() in app/views/admin/edit_outcome.php
- The function get_sector_by_id() is called to retrieve sector info, but it does not exist anywhere in the codebase.

## Solution Steps
- [x] Investigate if a similar function exists (e.g., get_sector_name) and its location.
- [x] Confirm that edit_outcome.php is an admin view, so the function should be in app/lib/admins/statistics.php.
- [ ] Implement get_sector_by_id($sector_id) in app/lib/admins/statistics.php to return the full sector row (associative array) or null if not found.
- [ ] Ensure the function uses parameterized queries for security.
- [ ] Test the fix by reloading the edit_outcome.php page.

## Notes
- Follow project conventions for function naming and file organization.
- Document the new function with PHPDoc.
- Suggest refactoring: If similar sector functions exist in multiple places, consider centralizing them.
