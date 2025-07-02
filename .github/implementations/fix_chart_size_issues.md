# Fix Chart Size and Responsiveness Issues

## Problem
The chart is still appearing small despite increasing the container height to 600px. The Canvas element has limited dimensions (280px height and 300px width) with inline styles that override our container settings.

## Analysis
- Chart.js is applying its own dimensions to the canvas element via inline styles
- These inline styles take precedence over our container height setting
- We need to ensure the chart respects the container dimensions and is properly responsive
- Current chart configuration might not be correctly applying responsive options

## Solution Steps

### Step 1: Improve chart container and canvas styling
- [x] Add explicit width to the chart container (100% of available space)
- [x] Use !important flag to ensure our height setting takes precedence
- [x] Apply minimum dimensions to the canvas element
- [x] Add specific responsive design settings

### Step 2: Update Chart.js configuration
- [x] Review and enhance responsive configuration options
- [x] Ensure maintainAspectRatio is set to false to allow custom dimensions
- [x] Set responsive to true to ensure proper resizing on different screen sizes
- [x] Added resizeDelay and devicePixelRatio options for better rendering

### Step 3: Add window resize listener
- [x] Add an event listener for window resize to redraw the chart when needed
- [x] Ensure chart is properly sized when the tab is shown
- [x] Added explicit canvas sizing before chart creation

## Files Modified
- `app/views/agency/outcomes/view_outcome.php` - Updated the chart container CSS and Chart.js configuration

## Changes Made
1. Enhanced the chart container styling:
   - Set width to 100% to use all available space
   - Added !important flag to the height property to ensure it takes precedence
   - Set minimum dimensions for the canvas element

2. Improved Chart.js configuration:
   - Added resizeDelay option to improve resize performance
   - Set devicePixelRatio for better rendering on high-DPI displays

3. Added explicit canvas sizing:
   - Set canvas dimensions to match container before chart creation
   - Added window resize listener to ensure chart adapts to window size changes

4. Added code to resize the chart when window size changes for better responsiveness
