# Fix Null Array Error in Manage Outcomes

**Date:** May 26, 2025  
**Status:** ✅ COMPLETED  

## Problem
PHP Fatal error occurs in `manage_outcomes.php` at line 146: `array_values(): Argument #1 ($array) must be of type array, null given`

## Root Cause Analysis
The code is using a variable `$metrics` that is null, but it should be using `$outcomes` which is properly defined from `get_all_outcomes_data()`.

## Issues Identified
1. ✅ **Variable Mismatch**: Code uses `$metrics` but should use `$outcomes`
2. ✅ **Null Value**: `$metrics` is never defined, so it's null
3. ✅ **array_values() Error**: Function fails when passed null instead of array

## Implementation Steps

### Step 1: Fix Variable Name
- [x] Replace `$metrics` with `$outcomes` in the filter section
- [x] Update the array_values() call to use correct variable
- [x] Ensure proper null checking

### Step 2: Add Null Safety
- [x] Add null checks before array operations
- [x] Provide fallback empty array if data is null
- [x] Test with empty data scenarios

### Step 3: Testing
- [x] Test page loads without errors
- [x] Test with and without filter parameters
- [x] Verify outcomes display correctly

## Files Fixed
- [x] `app/views/admin/outcomes/manage_outcomes.php` (fixed variable references and null safety)

## Changes Made
1. **Fixed Filter Logic** (lines 41-46): Changed `$metrics` to `$outcomes` and added null safety
2. **Fixed Array Values Call** (line 145): Changed to `$display_outcomes = !empty($outcomes) ? array_values($outcomes) : [];`
3. **Updated Variable References**: Changed all `$metric` variables to `$outcome` throughout the file
4. **Added Null Checks**: Ensured array operations are safe with proper null checking

## Expected Outcome
- ✅ No more PHP fatal errors
- ✅ Proper outcomes display
- ✅ Working filters and data display
