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
    - [x] Refactored entire initiatives API to use config-based table/column names
    - [x] Ensure all SQL queries in API use proper config variables

---

## Status

- All phases complete. The initiatives module is now fully consistent with the centralized db_names.php configuration.
- The deactivate button issue has been resolved by updating the API to use config-based names and properly handle the toggle_status action.

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

---

**Next Steps:**
- Monitor for any further issues or edge cases.
- Apply similar refactoring to any new modules or features as needed. 