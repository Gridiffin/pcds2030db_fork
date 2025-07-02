# Fix Chart Not Rendering on Initial Tab Selection

## Problem
The chart doesn't render when the Chart View tab is initially selected. Users need to interact with a button first (like changing chart type or toggling cumulative view) before the chart appears.

## Analysis
- The chart initialization is properly set up to run when the tab is shown using the `shown.bs.tab` event
- However, there seems to be a timing issue where the chart data may not be fully processed or Chart.js might not be fully loaded when the tab is first shown
- The chart canvas is available but the rendering doesn't happen until a user interaction triggers a re-initialization

## Solution Steps

### Step 1: Improve the tab activation handler
- [x] Ensure the chart initialization runs properly when the Chart tab is selected
- [x] Add a small delay to ensure all DOM elements are fully loaded and accessible (increased from 100ms to 300ms)
- [x] Verify that Chart.js is loaded before attempting to render the chart

### Step 2: Implement a direct chart initialization on tab show
- [x] Force chart initialization when the tab is shown without requiring user interaction
- [x] Add code to properly handle the case where the Chart tab is the initially selected tab
- [x] Create a function to explicitly initialize the chart when the tab becomes visible

### Step 3: Add a fallback mechanism
- [x] Add a visibility check that attempts to render the chart if it's not already rendered
- [x] Implement a better loading sequence for Chart.js
- [x] Added console logging for easier troubleshooting

## Files Modified
- `app/views/agency/outcomes/view_outcome.php` - Updated the JavaScript code for chart initialization

## Changes Made
1. Restructured the chart initialization logic for better control flow
2. Created a dedicated `setupChartInitialization()` function to handle all chart initialization scenarios
3. Added detection for when the chart tab is initially active
4. Increased the initialization delay from 100ms to 300ms for better reliability
5. Added console logging for easier troubleshooting
6. Force chart re-initialization on tab show for consistent behavior
7. Improved Chart.js loading sequence
