# Fix Chart Rendering Issue

## Problem
Charts are not rendering on the agency-side view_outcome.php despite:
- Chart.js being loaded
- Chart initialization functions being called
- Data being passed correctly to JavaScript
- No JavaScript errors in console logs

## Root Cause Analysis
Based on the console logs showing:
- `initializeChart called`
- `Chart data loaded`
- `Chart initialization for view mode completed`

But charts still not appearing, the likely issues are:

1. **Script Loading Order**: chart-manager.js was loaded after view-outcome.js, causing function availability issues
2. **Timing Issue**: Chart.js library not fully loaded when chart initialization is attempted
3. **Canvas Visibility**: Chart canvas not visible when trying to render (hidden tab)
4. **Duplicate Script Tags**: Causing JavaScript execution errors
5. **Chart Creation Error**: Silent failures in Chart.js instantiation

## Solution Implementation

### 1. Fixed JavaScript Script Loading Order (CRITICAL FIX)
- [x] **Fixed script loading order**: chart-manager.js now loads before view-outcome.js
- [x] **Root cause**: view-outcome.js was trying to call functions from chart-manager.js before it was loaded
- [x] This was causing `prepareChartData` and `initializeOutcomeChart` functions to be undefined
- [x] Fixed duplicate `</script>` tag in view_outcome.php
- [x] Added `waitForChart()` function to ensure Chart.js is loaded before initialization
- [x] Added Chart.js availability check in `initializeChart()`

### 2. Enhanced Debugging and Error Detection
- [x] Added comprehensive function availability checking
- [x] Added multiple fallback methods for calling chart functions
- [x] Enhanced logging to track exactly where the chart creation process fails
- [x] Added debugging for both direct function calls and window object access

### 3. Fixed JavaScript Timing Issues
- [x] Enhanced chart tab click event handling
- [x] Added initialization for already-active chart tab
- [x] Added proper timing delays for DOM and chart library readiness

### 4. Improved Chart Tab Handling
- [x] Enhanced chart tab click event handling
- [x] Added initialization for already-active chart tab
- [x] Added proper timing delays for DOM and chart library readiness

### 5. Enhanced Chart Creation
- [x] Added Chart.js version logging for debugging
- [x] Added explicit Chart.js availability check in chart-manager.js
- [x] Added force update after chart creation
- [x] Improved error handling and logging

### 6. Better Error Handling
- [x] Added comprehensive error logging with stack traces
- [x] Added debugging for Chart.js library availability
- [x] Added canvas context validation
- [x] Added function availability checks for both direct and window object access

## Files Modified

1. **app/views/agency/outcomes/view_outcome.php**
   - **CRITICAL**: Fixed script loading order (chart-manager.js before view-outcome.js)
   - Fixed duplicate script tag
   - Implemented `waitForChart()` function
   - Better timing for initialization

2. **assets/js/outcomes/view-outcome.js**
   - **CRITICAL**: Added function availability checks for chart-manager.js functions
   - Added Chart.js availability check
   - Enhanced chart tab handling
   - Added multiple fallback methods for function calls
   - Better error handling and debugging

3. **assets/js/outcomes/chart-manager.js**
   - Added Chart.js validation
   - Enhanced error logging
   - Added force chart update

## Testing Steps

1. Open an outcome in view mode
2. Click on the "Chart View" tab
3. Check browser console for:
   - Chart.js loading confirmation
   - Chart initialization logs
   - Any error messages
4. Verify chart renders correctly

## Expected Results

After these changes:
- Charts should render properly when the Chart View tab is clicked
- Better error messages if chart creation fails
- Proper timing to avoid race conditions between DOM, Chart.js, and chart initialization

## Debugging Information

If charts still don't render, check console for:
- "Chart.js loaded, initializing view outcome"
- "Chart created successfully"
- Any error messages in chart creation

## Status
- [x] Implemented timing fixes
- [x] Fixed script tag issues
- [x] Enhanced error handling
- [x] **CRITICAL FIX**: Fixed script loading order - chart renders but shows no data
- [ ] **NEW ISSUE**: Chart loads but filteredColumns is empty - need to debug column selection logic
- [ ] User testing required
