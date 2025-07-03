# Fix Table Sorting Issues

## Problem
The Initiative and Progress Rating columns are not properly sortable in both the draft and finalized programs tables.

## Issues Identified

1. **Incorrect Column Indexing**: The table_sorting.js file is using hardcoded column indices that don't match the actual table structure
2. **Missing Initiative Sorting**: No sorting logic for initiative column
3. **Incorrect Rating Column Index**: Rating column is being referenced as 2nd column when it's actually 3rd
4. **Missing Data Attributes**: Initiative and rating data not properly accessible for sorting

## Current Table Structure
1. Program Information (Column 1)
2. Initiative (Column 2) 
3. Progress Rating (Column 3)
4. Last Updated (Column 4)
5. Actions (Column 5)

## Tasks to Fix

- [x] Update table_sorting.js to use correct column indices
- [x] Add initiative sorting logic
- [x] Fix rating column sorting logic
- [x] Add data attributes for better sorting
- [x] Test sorting functionality on both tables
- [x] Ensure sorting works with filtering

## Implementation Plan

1. **Fix Column Indices**: ✅ Updated hardcoded column references
2. **Add Initiative Sorting**: ✅ Sort by initiative name alphabetically with proper handling of programs without initiatives
3. **Fix Rating Sorting**: ✅ Use proper rating hierarchy with data attributes
4. **Add Data Attributes**: ✅ Added sortable data attributes to table cells
5. **Test Functionality**: ✅ Verified sorting works correctly

## Changes Made

### Files Modified:
1. **`assets/js/utilities/table_sorting.js`**
   - Fixed column indexing for initiative (column 2) and rating (column 3)
   - Added proper initiative sorting logic using data attributes
   - Improved rating sorting with data-rating-order attributes
   - Added handling for programs without initiatives

2. **`app/views/agency/programs/view_programs.php`**
   - Added `data-initiative` and `data-initiative-id` attributes to initiative columns
   - Added `data-rating` and `data-rating-order` attributes to rating columns
   - Enhanced rating badges with icons and improved styling
   - Applied changes to both draft and finalized program tables

### Technical Details:
- **Initiative Sorting**: Uses `data-initiative` attribute, with programs without initiatives sorted to the end
- **Rating Sorting**: Uses `data-rating-order` with numerical priority (1=best, 6=worst)
- **Data Attributes**: Reliable sorting data attached to table cells
- **Cross-Browser Compatible**: Uses standard DOM methods

### User Impact:
- ✅ Initiative column now sorts alphabetically (A-Z / Z-A)
- ✅ Progress Rating column sorts by priority (Best to Worst / Worst to Best)
- ✅ Programs without initiatives appear at the end when sorting by initiative
- ✅ Sorting works on both draft and finalized program tables
- ✅ Sorting is preserved when applying filters

## Status: Complete
## Status: Complete
- **Started**: 2025-01-03
- **Completed**: 2025-01-03
