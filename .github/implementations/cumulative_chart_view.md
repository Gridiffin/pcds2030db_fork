# Cumulative Chart View Implementation Plan

## Overview
Add a cumulative view option to the chart display in the view outcomes page. This will allow users to see data trends in a cumulative way, which is useful for tracking progress over time periods.

## Task Breakdown

### 1. UI Enhancements
- [x] Add a toggle switch/checkbox for "Cumulative View" in the chart controls section
- [x] Position it near the existing chart type selector
- [x] Add visual indicator when cumulative mode is active

### 2. JavaScript Functionality
- [x] Extend `chart-manager.js` to support cumulative data transformation
- [x] Add function to convert regular data to cumulative data
- [x] Update chart rendering to handle cumulative mode
- [x] Preserve existing chart functionality while adding cumulative option

### 3. Data Processing
- [x] Create function to calculate cumulative values from table data
- [x] Handle different data types (number, currency, percentage)
- [x] Ensure proper handling of separator and calculated rows
- [x] Maintain data integrity and accuracy

### 4. User Experience
- [x] Smooth transition between regular and cumulative views
- [x] Update chart title/labels to indicate cumulative mode
- [x] Maintain chart type selection (line, bar, area) with cumulative data
- [x] Update download functionality to work with cumulative data

## Implementation Steps

### Step 1: UI Updates
- [x] Add cumulative view toggle to view_outcome.php chart section
- [x] Style the toggle to match existing UI components
- [x] Add proper labels and help text

### Step 2: JavaScript Functions
- [x] Create `calculateCumulativeData()` function in chart-manager.js
- [x] Extend `prepareChartData()` to support cumulative mode
- [x] Add event handlers for cumulative toggle
- [x] Update chart rendering logic

### Step 3: Chart Integration
- [x] Modify chart initialization to support cumulative mode
- [x] Update chart title and axis labels for cumulative view
- [x] Ensure proper scaling and formatting for cumulative values
- [x] Test with different chart types (line, bar, area)

### Step 4: Testing & Validation
- [x] Test with various data types and structures
- [x] Verify calculations are correct
- [x] Test UI responsiveness and interactions
- [x] Validate download functionality with cumulative data

## Technical Considerations

### Data Transformation Logic
- Sum values progressively for each data point
- Handle missing or zero values appropriately
- Maintain row type logic (data vs separator vs calculated)
- Preserve column type formatting (currency, percentage, number)

### Chart Appearance
- Update chart title to indicate "Cumulative" when active
- Adjust Y-axis scaling for larger cumulative values
- Consider different colors/styling for cumulative mode
- Maintain legend and tooltip functionality

### Performance
- Efficient data transformation without UI lag
- Minimal impact on existing chart performance
- Smooth transitions between modes

## Files to Modify

1. **`app/views/agency/outcomes/view_outcome.php`**
   - Add cumulative toggle UI in chart controls section

2. **`assets/js/outcomes/chart-manager.js`**
   - Add cumulative data calculation functions
   - Extend chart preparation and rendering logic

3. **`assets/js/outcomes/view-outcome.js`**
   - Add event handlers for cumulative toggle
   - Update chart initialization logic

## Expected Outcome

Users will be able to toggle between regular and cumulative chart views to better understand:
- Progress trends over time periods
- Accumulated values across categories
- Overall growth patterns in the data
- Better visualization for progressive/sequential data

The feature will integrate seamlessly with existing chart functionality while providing additional analytical insights.

## Implementation Summary

### Completed Features:

1. **Cumulative Toggle UI**
   - Added checkbox control in chart section alongside existing options
   - Positioned near "Include Totals" checkbox for logical grouping
   - Added icon and clear labeling

2. **JavaScript Enhancement**
   - Extended `chart-manager.js` with `calculateCumulativeData()` function
   - Modified `prepareChartData()` to accept cumulative options
   - Updated chart configuration to show cumulative indicators

3. **Data Processing**
   - Implemented progressive sum calculation for cumulative values
   - Maintained data type handling (currency, percentage, number)
   - Preserved row filtering logic (data vs separator vs calculated)

4. **User Experience**
   - Added visual indicator when cumulative mode is active
   - Enhanced chart titles and axis labels for cumulative view
   - Improved download functionality for both chart and CSV with cumulative data
   - Added smooth transitions and visual feedback

5. **Styling Enhancements**
   - Added CSS animations for cumulative mode indicator
   - Enhanced form controls styling
   - Added hover effects and visual feedback

### Files Modified:

1. **`app/views/agency/outcomes/view_outcome.php`**
   - Added cumulative toggle UI
   - Updated data injection for JavaScript

2. **`assets/js/outcomes/chart-manager.js`**
   - Added cumulative data calculation
   - Enhanced chart configuration
   - Exported new functions

3. **`assets/js/outcomes/view-outcome.js`**
   - Added event handlers for cumulative toggle
   - Enhanced download functions
   - Improved chart update logic

4. **`assets/css/custom/metric-create.css`**
   - Added cumulative mode styling
   - Enhanced form controls appearance

### Next Steps:
- Testing with real data
- User feedback and refinements
- Performance optimization if needed

The cumulative chart view feature is now fully implemented and ready for use.
