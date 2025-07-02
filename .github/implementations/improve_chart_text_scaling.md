# Improve Chart Formatting and Text Scaling

## Problem
While the chart is now properly sized, the text scaling doesn't look good when stretched out. Labels, titles, and grid lines appear distorted.

## Analysis
- We've successfully increased the chart size, but text elements don't scale well with the increased dimensions
- Chart.js needs additional configuration to handle font sizes and other visual elements at larger sizes
- We need to balance the chart size with proper text/element scaling for a professional appearance

## Solution Steps

### Step 1: Refine container dimensions
- [x] Adjusted the chart container to a more reasonable height (500px instead of 600px)
- [x] Added background color and padding for better visual presentation
- [x] Used border-radius to make the chart container more visually appealing
- [x] Removed the !important flag to let Chart.js handle responsive behavior properly

### Step 2: Improve text and element scaling
- [x] Added font size and styling configuration to Chart.js options
- [x] Set appropriate tick sizes for x and y axes (11px font size)
- [x] Configured grid line thickness and color (thinner, lighter lines)
- [x] Configured title (16px bold) and legend (12px) font sizes explicitly
- [x] Enhanced tooltips with better styling and font sizes
- [x] Added padding and spacing to all chart elements

### Step 3: Apply styling best practices
- [x] Used padding instead of margin for better spacing
- [x] Applied consistent styling for chart elements (axes, title, legend)
- [x] Added layout padding to give chart breathing room
- [x] Improved x-axis label rotation handling for better readability
- [x] Fixed canvas initialization to better handle dimensions

## Files Modified
- `app/views/agency/outcomes/view_outcome.php` - Updated chart container and Chart.js configuration

## Changes Made
1. **Refined Container Styling:**
   - Changed height from 600px to 500px for better proportions
   - Added subtle background color (#fafafa)
   - Added 15px padding and 8px border radius for visual appeal

2. **Enhanced Text & Element Scaling:**
   - Set specific font sizes for all text elements
   - Improved axis tick formatting and styling
   - Added padding to prevent text clipping
   - Configured grid lines to be lighter and thinner

3. **Improved Chart.js Configuration:**
   - Added layout padding for better spacing
   - Enhanced tooltips with better styling
   - Improved title and legend configuration
   - Fixed canvas initialization to prevent dimension conflicts
