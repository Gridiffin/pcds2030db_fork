# Fix Period Constraint Violation Error

## Problem
Fatal error: Uncaught mysqli_sql_exception: Check constraint 'chk_valid_period_numbers' is violated.

## Root Cause Analysis
- Error occurs in `app/lib/functions.php:203` during `auto_manage_reporting_periods()` function
- Called from `app/lib/session.php:15` during login process
- The constraint `chk_valid_period_numbers` is being violated when inserting/updating period data

## Tasks
- [ ] Investigate the `chk_valid_period_numbers` constraint definition
- [ ] Examine the `auto_manage_reporting_periods()` function in functions.php
- [ ] Check the database schema for period-related tables
- [ ] Identify what data is violating the constraint
- [ ] Fix the constraint violation by either:
  - [ ] Updating the data to comply with the constraint
  - [ ] Modifying the constraint if it's too restrictive
  - [ ] Fixing the logic in `auto_manage_reporting_periods()`
- [ ] Test the fix
- [ ] Update documentation

## Progress
- [x] Initial investigation started
- [x] Root cause identified: auto_manage_reporting_periods() uses period_number 5-6 for half-yearly periods, but constraint only allows 1-2
- [ ] Fix the period numbering logic in auto_manage_reporting_periods()
- [ ] Test the fix 