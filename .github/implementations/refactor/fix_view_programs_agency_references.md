# Fix Agency Group References in view_programs.php

## Problem
The `app/views/agency/programs/view_programs.php` file still contains references to the old `agency_group` structure, which prevents agency users from properly viewing their programs.

## Root Cause
- Session variables use `$_SESSION['agency_group_id']` instead of `$_SESSION['agency_id']`
- SQL queries reference `u.agency_group_id` instead of `u.agency_id`
- Database column references need to be updated

## Solution Plan
- [x] Update session variable references from `agency_group_id` to `agency_id`
- [x] Update SQL queries to use `agency_id` column
- [x] Update variable names for consistency
- [ ] Test the changes to ensure agency users can see their programs
- [x] Update the progress tracker

## Files to Modify
- `app/views/agency/programs/view_programs.php`

## Changes Made
1. **Session Variables**: Updated `$_SESSION['agency_group_id']` to `$_SESSION['agency_id']`
2. **SQL Queries**: Updated `u.agency_group_id` to `u.agency_id` in WHERE clauses
3. **Variable Names**: Updated `$agency_group_id` to `$agency_id` for consistency
4. **Parameter Binding**: Updated bind_param calls to use the new variable names

## Progress
- [x] Analysis complete
- [x] Implementation complete
- [ ] Testing pending 