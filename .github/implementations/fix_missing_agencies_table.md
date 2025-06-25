# Fix Missing Agencies Table Error

## Problem Description
Fatal error: "Table 'pcds2030_dashboard.agencies' doesn't exist" on line 364 of edit_program.php. The code is trying to query an agencies table that doesn't exist in the database.

## Investigation Tasks
- [x] Identify the error location in edit_program.php:364
- [x] Check the actual database schema to see where agency data is stored
- [x] Fix the SQL query to use the correct table
- [x] Update any other references to the non-existent agencies table

## Root Cause Identified ✅
The code was trying to query `SELECT agency_id, agency_name FROM agencies` but:
- No `agencies` table exists in the database
- Agency data is stored in the `users` table
- Agencies have `role = 'agency'` in the users table
- The primary key is `user_id` (not `agency_id`)

## Database Schema Confirmed
- **users table**: Contains agency data with `user_id`, `agency_name`, `role`, `is_active`
- **sectors table**: Exists and is correct ✅
- **No agencies table**: Agencies are users with role = 'agency'

## Solution Applied ✅
- [x] Fixed agencies query to: `SELECT user_id as agency_id, agency_name FROM users WHERE role = 'agency' AND is_active = 1 ORDER BY agency_name`
- [x] Added proper filtering for active agencies only
- [x] Used alias `user_id as agency_id` to maintain compatibility with existing form code

## Solution Steps
- [x] Check database schema for user/agency tables
- [x] Fix the agencies query in edit_program.php
- [x] Check for similar issues with sectors table
- [x] Test the corrected functionality

## Files Modified
- [x] `app/views/admin/programs/edit_program.php` - Fixed agencies query

## Issue Resolution Complete ✅
The missing agencies table error has been fixed. The admin edit program should now load the agency dropdown correctly using data from the users table.
