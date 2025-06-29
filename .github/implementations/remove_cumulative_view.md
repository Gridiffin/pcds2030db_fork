# Remove Cumulative View Functionality

## Problem
Chart display is still not working after attempting data format and initialization fixes. The cumulative view functionality may be interfering with basic chart rendering. Need to remove cumulative functionality to isolate and fix the core chart display issue.

## Tasks

- [x] Remove cumulative view toggle from view_outcome.php
- [x] Remove cumulative-related JavaScript code from view-outcome.js
- [x] Simplify chart-manager.js to remove cumulative functionality
- [x] Remove cumulative-related event handlers and logic
- [ ] Test basic chart rendering without cumulative features
- [ ] Clean up any cumulative-related CSS or styling

## Files to Modify

1. `app/views/agency/outcomes/view_outcome.php` - Remove cumulative UI elements
2. `assets/js/outcomes/view-outcome.js` - Remove cumulative logic
3. `assets/js/outcomes/chart-manager.js` - Simplify chart functions
4. Any cumulative-related CSS files

## Implementation Steps

### Step 1: Remove UI Elements
- [x] Remove cumulative view checkbox and label from view_outcome.php
- [x] Remove any cumulative-related styling or containers

### Step 2: Simplify JavaScript
- [x] Remove cumulative logic from prepareChartData function
- [x] Remove cumulative event handlers from view-outcome.js
- [x] Simplify chart initialization to basic functionality

### Step 3: Test Basic Chart
- [ ] Verify chart renders with simplified data
- [ ] Test chart type switching (line, bar, area) 
- [ ] Confirm chart controls work properly
- [ ] Check console logs for debugging information

### Step 4: Clean Up
- [ ] Remove unused cumulative functions ✅
- [ ] Clean up any cumulative-related comments or code ✅  
- [ ] Update documentation if needed

## Changes Made

### UI Removed:
- Cumulative view checkbox and label
- Cumulative-related styling

### JavaScript Simplified:
- `prepareChartData()` - removed cumulative parameter and logic
- `updateChart()` - removed cumulative data processing
- `setupChartEventHandlers()` - removed cumulative view event handler
- `downloadChartImage()` - simplified filename logic
- `downloadDataCSV()` - removed cumulative calculations
- `initializeOutcomeChart()` - removed cumulative-specific chart title logic
- Removed `calculateCumulativeData()` function entirely

### Debugging Added:
- Comprehensive console logging for chart initialization process
- Data validation and error checking
- Chart.js library loading verification
