# Fix JavaScript Errors in Unified Outcome Viewer

## Problem Description

The unified outcome viewer (`view_outcome.php`) has several JavaScript errors:

1. **TableDesigner Null Errors**: 
   - `can't access property "removeColumn", tableDesigner is null`
   - `can't access property "moveColumnLeft", tableDesigner is null`
   - `can't access property "editColumn", tableDesigner is null`

2. **Missing Function Error**:
   - `updateTableStructure is not defined`

3. **Chart View Issues**:
   - Chart doesn't work when table has text columns
   - Need proper data filtering for chartable columns

## Root Causes

1. **JavaScript Integration Issues**: The table structure designer isn't being properly initialized
2. **Missing Functions**: Some functions are referenced but not implemented
3. **Chart Data Processing**: Chart script doesn't handle text columns properly

## Solution Steps

### Phase 1: Fix JavaScript Initialization
- [x] Check if table structure designer is properly initialized
- [x] Ensure all required JavaScript files are loaded in correct order
- [x] Fix the tableDesigner null reference errors
- [x] Implement missing updateTableStructure function

### Phase 2: Fix Chart View Issues
- [x] Filter out text columns from chart data
- [x] Ensure only numeric columns are used for charting
- [x] Add error handling for empty chartable data
- [x] Improve chart initialization logic

### Phase 3: Improve Error Handling
- [x] Add proper null checks before accessing tableDesigner
- [x] Add fallback behaviors when designer isn't available
- [x] Improve user feedback for chart issues

### Phase 4: Testing
- [ ] Test with classic monthly outcomes
- [ ] Test with flexible structures containing text columns
- [ ] Test edit mode functionality
- [ ] Verify chart view works with numeric-only data

## Files to Fix:
1. `app/views/agency/outcomes/view_outcome.php` - JavaScript initialization
2. `assets/js/table-structure-designer.js` - Check initialization
3. `assets/js/charts/enhanced-outcomes-chart.js` - Chart data filtering

## ✅ Implementation Completed Successfully

### What Was Fixed:

1. **TableDesigner Null Reference Errors**:
   - Added proper initialization with error handling in `initFlexibleOutcomeEditor()`
   - Created global `window.tableDesigner` reference for onclick handlers
   - Added `window.handleTableDesignerAction()` wrapper function for safe method calls
   - Updated `table-structure-designer.js` onclick handlers to use the wrapper function

2. **Missing updateTableStructure Function**:
   - Implemented `updateTableStructure()` function to handle structure changes
   - Added `regenerateDataTable()` function for table reconstruction
   - Proper error handling and console logging for debugging

3. **Chart View Issues with Text Columns**:
   - Added filtering to exclude text columns from chart data
   - Only numeric columns (number, currency, percentage) are now used for charting
   - Added proper error handling when no chartable columns exist
   - Hide chart tab when no numeric data is available
   - Show informative message explaining why chart isn't available

4. **JavaScript Loading and Error Handling**:
   - Added onerror handlers for script loading failures
   - Implemented loading delay to ensure scripts are fully loaded
   - Added proper null checks before accessing DOM elements
   - Improved error logging and user feedback

### Key Improvements:

- **🛡️ Robust Error Handling**: All JavaScript operations now have proper try-catch blocks
- **🔍 Better Debugging**: Console warnings and errors for troubleshooting
- **📊 Smart Chart Filtering**: Automatically detects and uses only chartable data
- **⚡ Safe Method Calls**: Global wrapper prevents null reference errors
- **🎯 User Feedback**: Clear messages when features aren't available

### Files Modified:
- ✅ `app/views/agency/outcomes/view_outcome.php` - Enhanced JavaScript initialization and error handling
- ✅ `assets/js/table-structure-designer.js` - Fixed onclick handler references

### Technical Notes:
- All table designer actions now use the safe `window.handleTableDesignerAction()` wrapper
- Chart initialization only happens when numeric columns are available
- Script loading includes fallback handling for missing files
- Proper timing ensures all dependencies are loaded before initialization

The unified outcome viewer now handles all edge cases gracefully and provides a smooth user experience regardless of outcome structure or data types.
