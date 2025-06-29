# Fix Outcome Chart Display Issue

## Problem
After migrating outcomes to unified custom structure, the chart view in `view_outcome.php` (agency side) doesn't show charts. The data is being passed to JavaScript but the chart isn't rendering.

## Console Output Analysis
```javascript
Data passed to JavaScript: 
Object { tableData: {…}, columns: (5) […], rows: (12) […], info: {…} }
​
columns: Array(5) [ {…}, {…}, {…}, … ]
​
info: Object { id: 7, tableName: "TIMBER EXPORT VALUE", isFlexible: true, … }
​
rows: Array(12) [ {…}, {…}, {…}, … ]
​
tableData: Object { January: (5) […], February: (5) […], March: (5) […], … }
```

## Root Cause Analysis - Updated

**Issue Identified**: Data format mismatch AND missing JavaScript initialization.

**Primary Issues Found**:
1. **Data Format Mismatch** (Fixed): JavaScript chart functions expected `tableData[row.id][column.id]` but received array format
2. **Missing Initialization** (New): After cumulative view implementation, chart initialization was not being called

**Current Data Format** (from migration):
```javascript
tableData: { 
  "January": [value1, value2, value3, ...], 
  "February": [value1, value2, value3, ...], 
  ... 
}
```

**Expected Format** (by prepareChartData):
```javascript
tableData[row.id][column.id] = value
// e.g., tableData["January"]["metric1"] = 123
```

**Missing Initialization**: The `initializeViewOutcome()` function was never being called, so chart setup was incomplete.

## Solution Implemented

### Phase 1: Data Format Fix
Modified `app/views/agency/outcomes/view_outcome.php` to transform the data into the expected format before passing to JavaScript.

### Phase 2: Initialization Fix  
Added proper JavaScript initialization call:
```javascript
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initializeViewOutcome === 'function') {
        initializeViewOutcome();
    }
});
```

**Root Cause**: The cumulative view implementation modified the chart system but the initialization call was missing from the view_outcome.php page, preventing charts from being set up properly.

## Solution Implemented

Modified `app/views/agency/outcomes/view_outcome.php` to transform the data into the expected format before passing to JavaScript:

1. **Data Transformation**: Added PHP logic to convert outcome data into `tableData[row.id][column.id]` format
2. **Flexible/Legacy Support**: Handles both flexible outcomes (with row_config/column_config) and legacy formats
3. **Backward Compatibility**: Preserves existing functionality while fixing chart display

**Code Changes**:
- Added data transformation logic in PHP before passing to JavaScript
- Maps row IDs and column IDs correctly
- Handles both indexed and named data access patterns

## Tasks

- [x] Examine current data passing in `app/views/agency/outcomes/view_outcome.php`
- [x] Check chart initialization in `assets/js/view-outcome.js`
- [x] Identify format mismatch between expected and actual data
- [x] Fix data format to match chart expectations
- [ ] Test chart rendering with corrected data format
- [ ] Verify fix works for different outcome types

## Files to Check/Modify

1. `app/views/agency/outcomes/view_outcome.php` - Data preparation and passing to JS
2. `assets/js/view-outcome.js` - Chart initialization and data processing
3. Possibly other chart-related JS files

## Implementation Steps

### Step 1: Analyze Current Data Passing
- [x] Check how `tableData` is constructed in PHP
- [x] Verify how `columns` and `rows` are built
- [x] Compare with what chart expects

### Step 2: Check Chart JavaScript
- [x] Examine chart initialization code
- [x] Identify expected data format
- [x] Find where the mismatch occurs

### Step 3: Fix Data Format
- [x] Modify data preparation in PHP  
- [x] Add proper JavaScript initialization call
- [x] Ensure backward compatibility

### Step 4: Testing
- [ ] Test chart rendering on existing outcomes
- [ ] Verify different data scenarios work
- [ ] Check both view modes (table and chart)
- [ ] Confirm console shows correct data format
- [ ] Verify chart initialization is called properly

## Testing Instructions

1. **Navigate to Agency Interface**: Go to an existing outcome view page
2. **Test Chart Tab**: Click on the "Chart" tab to trigger chart initialization
3. **Check Console**: Verify the console now shows data in format: `tableData[row.id][column.id]`
4. **Verify Chart Renders**: Confirm that charts now display properly

## Expected Results

**Before Fix**:
```javascript
tableData: { "January": [1,2,3], "February": [4,5,6], ... }
// Chart fails to render - wrong format
```

**After Fix**:
```javascript
tableData: { 
  "January": { "metric1": 1, "metric2": 2, ... },
  "February": { "metric1": 4, "metric2": 5, ... },
  ...
}
// Chart renders successfully - correct format
```
