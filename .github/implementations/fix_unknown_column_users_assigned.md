# Fix Unknown Column Errors in Database Queries

## Problem Description

- **First error**: `Unknown column 'p.users_assigned' in 'on clause'` in `app\lib\admins\statistics.php:534`
- **Third error**: `Unknown column 'rp.quarter' in 'field list'` in multiple files (reporting_periods table schema mismatch) ✅
- Error occurs when calling `get_admin_program_details(3)` from `app\views\admin\programs\view_program.php:37`
- The application is configured to use `pcds2030_db` database but SQL queries reference columns that don't exist

## Root Cause Analysis

- [x] Examined the SQL query in `app\lib\admins\statistics.php` around line 534
- [x] **CRITICAL DISCOVERY**: Application config points to `pcds2030_db` database (old schema)
- [x] The `pcds2030_db` database has different schema than `pcds2030_dashboard`:
  - **programs table**: uses `agency_id` (not `owner_agency_id` or `users_assigned`)
  - **users table**: uses `agency_id` (not `agency_name`)
  - **agency table**: EXISTS in old database (was missing in new database)
  - **reporting_periods table**: uses `period_type` and `period_number` (not `quarter`)
- [x] The queries were written for the wrong database schema

## Solution Steps

### Step 1: Investigate the error location

- [x] Read the problematic code in `app\lib\admins\statistics.php` around line 534
- [x] Understand what the query is trying to achieve

### Step 2: Check database schema

- [x] Use DBCode extension to examine the programs table structure
- [x] Identify the correct column names for user assignments

### Step 3: Fix the SQL queries for correct database schema

- [x] **MAJOR FIX**: Updated all queries to use `pcds2030_db` schema:
  - Changed `p.owner_agency_id` to `p.agency_id` (programs table structure)
  - Changed `u.agency_name` to `a.agency_name` (use agency table, not users)
  - Added proper JOIN with agency table: `JOIN agency a ON u.agency_id = a.agency_id`
- [x] Fixed all references in `app\lib\admins\statistics.php` (lines 51, 54, 534)
- [x] Fixed all references in `app\lib\agencies\statistics.php` (lines 86, 102, 153, 178, 245, 249, 277)
- [x] Fixed reference in `app\lib\outcome_automation.php` (line 194)
- [x] Test the query to ensure it works properly

### Step 4: Test the fix

- [x] Verified that `view_program.php?id=3` loads without errors
- [x] Confirmed the database connections and queries work properly with correct schema
- [x] Dashboard is accessible and functional with proper data display

## Implementation Details

- **Fixed SQL queries to match the actual database schema (`pcds2030_db`)**:

  - `app\lib\admins\statistics.php`:
    - Fixed `get_admin_program_details()` function (line 534)
    - Fixed `get_admin_agencies_overview()` function (lines 51, 54)
  - `app\lib\agencies\statistics.php`:
    - Fixed multiple instances using wrong column references
    - Restored proper joins to `agency` table (which exists in old DB)
    - Updated to use correct field mappings
  - `app\lib\outcome_automation.php`:
    - Fixed program selection query (line 194)
    - Fixed sector outcome data queries (lines 256, 286)
    - Restored agency table reference
  - `app\views\admin\programs\reopen_program.php`:
    - Fixed program submission query (line 36)
    - Changed `p.owner_agency_id` to `p.agency_id` and joined with `agency` table
  - `app\views\admin\ajax\recent_reports_table_new.php`:
    - Fixed reports query (line 22)
  - `app\api\delete_report.php`:
    - Fixed report details query (line 80)

- **Key changes for `pcds2030_db` schema compatibility**:

  - **programs table**: Uses `agency_id` (not `owner_agency_id` or `users_assigned`)
  - **users table**: Has `agency_id` field that links to agency table
  - **agency table**: EXISTS and contains `agency_name` field
  - **reporting_periods table**: Uses `period_type` and `period_number` (not `quarter`)
  - **JOIN pattern**: `programs.agency_id = users.agency_id = agency.agency_id`

- **Root cause**: Code was written for newer database schema but application configured to use older database
- **Solution**: Updated all queries to match the actual `pcds2030_db` database schema
