# Chart Size Fix for Admin View Implementation

## Problem
Similar to the agency view, the admin outcome chart has sizing issues that make the chart appear too small despite having a large container. This affects readability and makes the data visualization less useful.

## Solution
Apply the same chart fixes we successfully implemented on the agency side to the admin view:

1. ✅ Replace the chart container with a very large fixed-height div (800px)
2. ✅ Set explicit width and height styles on the canvas element itself
3. ✅ Simplify the Chart.js initialization code
4. ✅ Improve font sizes and styling for better readability
5. ✅ Add proper event handlers for tab switching and window resizing

## Changes Made

### HTML/Container Changes
```html
<!-- Chart Canvas - Simple Approach -->
<div style="width: 100%; height: 800px; margin: 20px 0;">
    <canvas id="metricChart" style="width: 100%; height: 100%;"></canvas>
</div>
```

### JavaScript Changes
1. Simplified chart initialization
2. Updated to use `window.currentChart` consistently
3. Increased font sizes throughout (14px to 20px)
4. Added better tooltip formatting with proper currency display
5. Added window resize handler

## Results
The admin view chart now:
1. Renders at a large, readable size
2. Maintains proper sizing when the window is resized
3. Has consistent styling with the agency view
4. Initializes correctly when switching tabs
