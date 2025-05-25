# Fix PHP Fatal Error in app/views/admin/manage_metrics.php

## Problem
A PHP Fatal error `TypeError: array_values(): Argument #1 ($array) must be of type array, null given` occurs in `D:\laragon\www\pcds2030_dashboard\app\views\admin\manage_metrics.php` on line 129.

## Steps to Solve
- [ ] 1. Read the code around line 129 in `app/views/admin/manage_metrics.php` to identify the null variable.
- [ ] 2. Determine the cause of the null variable.
- [ ] 3. Implement a check to ensure the variable is an array before calling `array_values()`, or initialize it as an empty array if appropriate.
- [ ] 4. Test the fix (manual verification by user after deployment).
- [ ] 5. Mark tasks as complete.
