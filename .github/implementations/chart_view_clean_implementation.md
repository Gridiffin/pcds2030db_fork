# Chart View Radical Clean Implementation

## Problem
The Chart.js implementation in the view_outcome.php file has persistent size issues. Despite setting a large chart container, the canvas element itself is rendering too small, making the chart difficult to read.

## Root Cause Analysis
- The canvas element sizing is either being overridden by global CSS or not properly initializing
- Multiple Chart.js configurations are creating conflicts
- CSS styles from metric-create.css may be interfering with container sizes

## Solution Approach
Instead of trying to fix the existing implementation, we implemented a complete rewrite with the simplest possible approach:

1. ✅ Replace the chart container with a very large fixed-height div (800px)
2. ✅ Set explicit width and height styles on the canvas element itself
3. ✅ Completely simplify the Chart.js initialization code
4. ✅ Reduce the complexity of chart options and focus on font sizes
5. ✅ Simplify event handlers for tab switching and window resizing

## Key Changes

### HTML Changes
```html
<!-- Chart Canvas - Simple Approach -->
<div style="width: 100%; height: 800px; margin: 20px 0;">
    <canvas id="metricChart" style="width: 100%; height: 100%;"></canvas>
</div>
```

### JavaScript Changes
1. Simplified chart initialization
2. Increased font sizes throughout (14px to 20px)
3. Removed complex event handling
4. Ensured Chart.js handles responsive resizing
5. Fixed chart instance reference (window.currentChart)

## Expected Results
- A very large, readable chart
- Responsive behavior on window resize
- Proper initialization when switching tabs
- No size conflicts from other CSS rules
