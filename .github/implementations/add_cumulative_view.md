# Add Cumulative View Functionality

## Problem
User wants to replace the "Include Totals" functionality with the cumulative view function that was previously removed during chart debugging.

## Solution
Add back cumulative view functionality as a chart display option, replacing the current "Include Totals" checkbox.

## Tasks

- [x] Replace "Include Totals" checkbox with "Cumulative View" checkbox in UI
- [x] Implement cumulative data transformation logic in chart-manager.js
- [x] Add cumulative view event handler in view-outcome.js
- [ ] Test cumulative view functionality with different chart types
- [ ] Ensure cumulative works with column selection

## Implementation Steps

### Step 1: Update UI Elements
- [x] Replace "Include Totals" checkbox with "Cumulative View" checkbox
- [x] Update labels and styling for cumulative view option

### Step 2: Implement Cumulative Logic
- [x] Add cumulative data transformation in prepareChartData function
- [x] Ensure cumulative works with filtered columns
- [x] Handle cumulative calculations for different data types

### Step 3: Update Event Handlers
- [x] Replace showTotals event handler with cumulative view handler
- [x] Update chart refresh logic to handle cumulative option

### Step 4: Testing
- [ ] Test cumulative view with line charts
- [ ] Test cumulative view with bar charts
- [ ] Test cumulative view with area charts
- [ ] Verify cumulative works with column filtering

## Files to Modify

1. `app/views/agency/outcomes/view_outcome.php` - Update UI elements
2. `assets/js/outcomes/view-outcome.js` - Update event handlers
3. `assets/js/outcomes/chart-manager.js` - Add cumulative logic

## Cumulative View Logic

Cumulative view transforms data so each point shows the running total:
- Regular: [10, 20, 15, 25] 
- Cumulative: [10, 30, 45, 70]

This is useful for tracking progress over time periods.
