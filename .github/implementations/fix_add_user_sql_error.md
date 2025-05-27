# Fix Add User SQL Error Issue

## Problem Description
After fixing the redirect path issue, the add user form now encounters a SQL error when submitting:
- **Error**: `PHP Fatal error: Uncaught mysqli_sql_exception: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '' at line 1`
- **Additional Error**: `Database error: Unknown column 'id' in 'field list'`
- **Location**: `app/views/admin/users/add_user.php:246` (but actually in the `add_user()` function)

## Root Cause Analysis
Located the source of the SQL error:
- [ ] Check database schema for correct column names
- [ ] Review the `add_user()` function in `app/lib/admins/users.php`
- [ ] Check the agency group validation query
- [ ] Verify sector validation query

**Root Cause**: The `add_user()` function in `app/lib/admins/users.php` at line 142 is using an incorrect column name `id` instead of `agency_group_id` when validating the agency group exists.

## Solution Steps

### Step 1: Verify Database Schema
- [ ] Check the `agency_group` table structure
- [ ] Confirm the correct column name for the primary key
- [ ] Verify the `sectors` table structure

### Step 2: Fix Column Name References
- [ ] Update the agency group validation query to use correct column name
- [ ] Ensure consistency with other queries in the same function
- [ ] Check for any other similar issues

### Step 3: Test the Fix
- [ ] Test adding a new admin user
- [ ] Test adding a new agency user
- [ ] Verify form validation works correctly
- [ ] Ensure database inserts work properly

### Step 4: Validation
- [ ] Check for any other SQL queries with incorrect column names
- [ ] Ensure the fix doesn't break other functionality
- [ ] Verify all user creation scenarios work

## Implementation Notes
- This is a follow-up to the redirect path fix
- The error only appeared after the form started submitting to the correct location
- Need to ensure database schema consistency throughout the application
- Use parameterized queries to prevent SQL injection (already implemented)
