# Initiatives Module: Centralized DB Names Refactor

## Goal
Refactor all initiatives-related backend code to use the centralized db_names.php config for all table and column names, eliminating hardcoded references and old helper usage. Ensure all SQL queries and logic use config-based variables for maintainability and future-proofing.

---

## TODO List

- [x] **Phase 1:** Refactor main initiatives library files (initiative_functions.php, agencies/initiatives.php) to use config-based table/column names. (Complete)
- [x] **Phase 2:** Update admin initiative views to use config-based names. (Complete)
- [x] **Phase 3:** Update agency initiative views to use config-based names. (Complete)
- [x] **Phase 4:** Refactor initiative_functions.php to remove all get_column_name/get_table_name usage and ensure all SQL queries use config-based variables. (Complete)
    - [x] Scan and update all SQL queries in initiative_functions.php
    - [x] Remove any remaining get_column_name/get_table_name usage
    - [x] Replace any hardcoded table/column names with config-based variables
    - [x] Ensure all logic is consistent and future-proof
- [x] **Phase 5:** Fix deactivate button issue in manage initiatives page. (Complete)
    - [x] Identify that API was using hardcoded column names instead of config-based names
    - [x] Add toggle_status action handling to initiatives API
    - [x] Refactor entire initiatives API to use config-based table/column names
    - [x] Ensure all SQL queries in API use proper config variables
- [x] **Phase 6:** Fix dashboard issues with sector_id and is_assigned columns. (Complete)
    - [x] Fix undefined sector_id warning in agency dashboard
    - [x] Fix unknown column 'p.is_assigned' error in DashboardController
    - [x] Update DashboardController to use config-based column names
    - [x] Implement proper logic for assigned vs owned programs
- [x] **Phase 7:** Fix agencies/initiatives.php column name issues. (Complete)
    - [x] Fix unknown column 'p.id' error in agencies/initiatives.php
    - [x] Refactor agencies/initiatives.php to use direct config-based names
    - [x] Remove usage of db_names_helper.php functions
    - [x] Ensure all SQL queries use config-based variables

---

## Status

- All phases complete. The initiatives module and dashboard are now fully consistent with the centralized db_names.php configuration.
- The deactivate button issue has been resolved by updating the API to use config-based names and properly handle the toggle_status action.
- Dashboard issues have been resolved by fixing column name mismatches and implementing proper sector determination logic.

---

## Issues Resolved

### Deactivate Button Error: "No field to be updated"
**Problem:** The deactivate button in manage initiatives was showing "no field to be updated" error.

**Root Cause:** The initiatives API (`app/api/initiatives.php`) was using hardcoded column names like `initiative_id`, `initiative_name`, etc., instead of the config-based names. This caused a mismatch between the expected column names and the actual database schema.

**Solution:** 
1. Updated the initiatives API to load and use the centralized db_names.php configuration
2. Added proper handling for the `toggle_status` action in the PUT handler
3. Refactored all SQL queries in the API to use config-based table and column names
4. Ensured the API calls the `toggle_initiative_status` function from initiative_functions.php

**Files Modified:**
- `app/api/initiatives.php` - Complete refactor to use config-based names

### Dashboard Issues: sector_id and is_assigned Columns
**Problem:** 
1. Warning: "Undefined array key 'sector_id'" in agency dashboard
2. Fatal error: "Unknown column 'p.is_assigned' error in 'field list'" in DashboardController
3. Fatal error: "Unknown column 'p.sector_id' in 'field list'" in agency dashboard

**Root Cause:** 
1. The `sector_id` column was removed from the users table in recent schema updates
2. The `is_assigned` column doesn't exist in the programs table - the logic should use `users_assigned` and `agency_id` columns
3. The `sector_id` column doesn't exist in the programs table either - the sector table is being removed entirely

**Solution:**
1. **sector_id fix**: Implemented simple agency-based mapping since sector table is being removed:
   - All forestry-related agencies (STIDC, SFC, FDS) map to Forestry sector (ID: 1)
   - Default to Forestry sector for any other agencies
2. **is_assigned fix**: Updated DashboardController to use proper logic:
   - Programs with `users_assigned IS NULL` are agency-owned programs
   - Programs with `users_assigned IS NOT NULL` are assigned programs
3. **Config-based names**: Updated DashboardController to use config-based table and column names throughout

**Files Modified:**
- `app/views/agency/dashboard/dashboard.php` - Fixed sector_id determination with agency-based mapping
- `app/controllers/DashboardController.php` - Complete refactor to use config-based names and correct logic

### Agencies Initiatives Column Name Error
**Problem:** Fatal error: "Unknown column 'p.id' in 'field list'" in agencies/initiatives.php

**Root Cause:** The agencies/initiatives.php file was still using the old db_names_helper.php approach with `get_column_name()` and `get_table_name()` functions, but the SQL queries were still referencing hardcoded column names like `p.id` instead of the mapped variables.

**Solution:** 
1. Refactored agencies/initiatives.php to use direct config-based column names from db_names.php
2. Removed all usage of `get_column_name()` and `get_table_name()` helper functions
3. Extracted all table and column names as variables at the top of the file
4. Updated all SQL queries to use the config-based variables consistently

**Files Modified:**
- `app/lib/agencies/initiatives.php` - Complete refactor to use direct config-based names

---

**Next Steps:**
- Monitor for any further issues or edge cases.
- Apply similar refactoring to any new modules or features as needed. 