# Fix Data Not Being Saved in Outcome Creation/Editing

## Current Status ✅❌
- [x] ✅ **Creation**: Saves data correctly 
- [x] ✅ **Editing**: Can edit and updates database correctly (100 → 1000 works)
- [x] ✅ **Backend**: Data persistence works
- [ ] ❌ **Frontend Display**: Values don't show up when page loads (always shows 0)

## Issue Identified 🎯
**FRONTEND DISPLAY PROBLEM**: Data is in database but not rendering on page load.

The problem is in the **data loading/rendering** logic, not data saving.

## Investigation Steps
- [ ] Check data collection in `create_outcome_flexible.php`
- [ ] Check data collection in `edit_outcomes.php` 
- [ ] Verify HTML input/cell structure matches JavaScript selectors
- [ ] Check JSON data format being saved vs expected format
- [ ] Debug data flow from form to database
- [ ] Test data loading from database back to form

## Root Cause Found! 🎯
**FRONTEND RENDERING BUG**: `renderTable()` was calling `collectCurrentData()` on initial load!

**The Bug Flow**:
1. PHP loads data: `{"row 1":{"column_1":100}}` ✅
2. JavaScript receives data correctly ✅  
3. `renderTable()` called on page load
4. `renderTable()` calls `collectCurrentData()` first
5. `collectCurrentData()` reads from empty DOM cells → overwrites data with 0s ❌
6. Table renders with 0 values ❌

## Solution Implemented ✅
**Fixed `renderTable()` function**:
- Added parameter `skipDataCollection = false`
- On initial page load: `renderTable(true)` - skip data collection, use loaded data
- On user actions: `renderTable()` - preserve existing data from DOM

## Problem Analysis
1. `edit_outcomes.php` has its own embedded JavaScript for data handling
2. But `edit-outcome.js` is also loading and taking over
3. These two systems have different data structure expectations
4. `edit-outcome.js` is probably meant for a different edit page

## Solution Implemented ✅
1. **Identified conflict**: `edit-outcome.js` was auto-loading on `edit_outcomes.php` page
2. **Added disable flag**: `window.editOutcomeJsDisabled = true` in `edit_outcomes.php`
3. **Modified `edit-outcome.js`**: Added check to prevent initialization when disabled
4. **Preserved debugging**: Left debug logs to verify data loading

## Testing Steps
1. Edit an existing outcome with saved data
2. Check console - should see our debug output, NOT the edit-outcome.js logs
3. Verify data values display correctly (not 0)
4. Test editing and saving works properly

## Debugging Added
Added console.log statements to track:
1. Data loaded from PHP to JavaScript
2. Row labels vs data keys matching
3. Column names vs data column keys matching
4. Cell value calculation process

## Test Instructions
1. Open browser console
2. Edit an existing outcome with saved data
3. Check console output for data matching issues

## Files to Check
- `app/views/agency/outcomes/create_outcome_flexible.php`
- `app/views/agency/outcomes/edit_outcomes.php`
- Data collection JavaScript functions
- HTML structure for inputs/cells
