# Database Names Mapping Fix

## Problem
The Create Program page and related functions are not following the centralized database names mapping defined in `app/config/db_names.php`. The configuration files were outdated and didn't match the actual database schema.

## Analysis

### Actual Database Schema (programs table) - Updated
- `program_id` (primary key)
- `initiative_id` (NOT NULL)
- `program_name` 
- `program_number` (varchar(50))
- `program_description` (text)
- `agency_id` (NOT NULL)
- `is_deleted` (tinyint(1), default 0)
- `created_by` (NOT NULL)
- `created_at` (timestamp)
- `updated_at` (timestamp)

### Issues Found
- [x] **RESOLVED**: Updated `database_schema.php` to match actual database structure
- [x] **RESOLVED**: Updated `db_names.php` to include all actual columns
- [x] **RESOLVED**: Added missing `program_description` column mapping
- [x] **RESOLVED**: Added missing `target_number` column to program_targets
- [ ] Form field `brief_description` should map to `program_description` column
- [ ] Verify all field mappings follow `db_names.php` conventions

## Solution Plan

### Task 1: Update configuration files ✅ COMPLETED
- [x] Updated `database_schema.php` to match actual database structure
- [x] Updated `db_names.php` to include all actual columns
- [x] Added missing `program_description` column mapping
- [x] Added missing `target_number` column to program_targets

### Task 2: Fix create_simple_program function ✅ COMPLETED
- [x] Added centralized database mapping at the top of programs.php file
- [x] Updated function to use correct column names from `db_names.php`
- [x] Map `brief_description` form field to `program_description` database column
- [x] Ensure all database operations use correct column names

### Task 3: Update form fields ✅ COMPLETED
- [x] Form field `brief_description` correctly maps to `program_description` database column
- [x] All form fields match the actual database schema
- [x] Field names follow `db_names.php` mapping conventions

### Task 4: Update related functions ✅ COMPLETED
- [x] Fixed `update_simple_program` function to use centralized column mapping
- [x] Updated all database operations to use correct column names
- [x] Ensured consistency with `db_names.php` configuration

### Task 5: Test and validate ✅ COMPLETED
- [x] Test program creation functionality
- [x] Verify data is stored correctly
- [x] Check that all form fields work as expected
- [x] Fixed delete program functionality (removed non-existent columns)
- [x] Added toast notification to view programs page

## Implementation Status
- [x] Task 1: Update configuration files
- [x] Task 2: Fix create_simple_program function
- [x] Task 3: Update form fields  
- [x] Task 4: Update related functions
- [x] Task 5: Test and validate

## Additional Fixes Completed

### Fix 1: Delete Program Functionality
- [x] Fixed `delete_program.php` - removed references to non-existent `owner_agency_id` and `is_assigned` columns
- [x] Updated query to use correct column names: `agency_id` and proper user agency lookup
- [x] Maintained security by checking user's agency ownership

### Fix 2: Toast Notification System
- [x] Added toast notification functionality to `view_programs.php`
- [x] Integrated with existing `showToast` function from `main.js`
- [x] Shows notifications for program creation, deletion, and other operations
- [x] Uses session-based message system for consistent user experience 