# Fix Chart View UI and Functionality Issues

## Problem Analysis
Several chart functionality issues identified:

1. **Chart Type Mismatch**: 
   - Default chart shows as bar but dropdown says "Line Chart"
   - Need to sync initial chart type with dropdown selection

2. **Cumulative View Toggle**:
   - Checkbox exists but doesn't trigger chart recalculation
   - Need to implement cumulative data calculation and chart update

3. **Download Chart Image**:
   - Button exists but has no functionality
   - Need to implement chart-to-image download using Chart.js built-in method

## Solution Implementation

### ✅ Phase 1: Fix Chart Type Synchronization
- [x] **Task 1.1**: Set dropdown default to match initial chart type (bar)
- [x] **Task 1.2**: Ensure chart type changes properly update the chart
- [x] **Task 1.3**: Test all chart types (line, bar, area)

### ✅ Phase 2: Implement Cumulative View
- [x] **Task 2.1**: Add cumulative data calculation function
- [x] **Task 2.2**: Wire cumulative checkbox to trigger chart update
- [x] **Task 2.3**: Test cumulative vs normal view switching

### ✅ Phase 3: Add Chart Download Functionality  
- [x] **Task 3.1**: Implement download chart as image using Chart.js toBase64Image()
- [x] **Task 3.2**: Wire download button to download function
- [x] **Task 3.3**: Test image download functionality

### ✅ Phase 4: Testing and Validation
- [x] **Task 4.1**: Test all chart functionality together
- [x] **Task 4.2**: Verify user interface consistency
- [x] **Task 4.3**: Ensure no JavaScript errors

## ✅ Implementation Complete

### Changes Made:

1. **Fixed Chart Type Synchronization**
   - Changed dropdown default option from "Line Chart" to "Bar Chart" to match initial chart display
   - Simplified chart type change handler to call `initializeChart()` directly
   - Added proper handling for "area" chart type (renders as line chart with fill)

2. **Implemented Cumulative View Functionality**
   - Added cumulative data calculation logic that creates running totals
   - Added event listener for cumulative view checkbox
   - Chart title and legend labels update to show "(Cumulative)" when enabled
   - Chart redraws automatically when cumulative view is toggled

3. **Added Chart Download Image Feature**
   - Implemented download functionality using Chart.js built-in `toBase64Image()` method
   - Chart instance stored globally as `window.currentChart` for access
   - Download button triggers image download as "outcome-chart.png"
   - Added error handling for cases where chart is not available

4. **Code Improvements**
   - Removed redundant chart creation code in event handlers
   - Centralized chart creation logic in `initializeChart()` function
   - Cleaned up debug console.log statements
   - Added proper chart destruction before recreation

### Functionality Verification:
- ✅ Chart type dropdown now matches displayed chart type on load
- ✅ Chart type switching works for bar, line, and area charts
- ✅ Cumulative view toggle properly recalculates and redraws data
- ✅ Download image button saves chart as PNG file
- ✅ Chart title updates to show cumulative status
- ✅ Legend labels include "(Cumulative)" when applicable

## Expected Changes
- ✅ Chart type dropdown and actual chart type will be synchronized
- ✅ Cumulative view toggle will recalculate and redraw chart data
- ✅ Download image button will save chart as PNG file

---
**Status**: ✅ **COMPLETE**  
**Priority**: Medium  
**Completion Time**: 1 hour
