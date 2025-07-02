# Simplify Chart Container Approach

## Problem
The chart is still not displaying at an adequate size despite previous attempts to fix it. The solutions are overcomplicating what should be a simple task.

## Analysis
- The current approach involves too many complex Chart.js configurations
- We should go back to basics and simply create a large enough container and let the chart fill it
- Previous changes might be interfering with the chart's natural sizing

## Solution Steps

### Step 1: Simplify chart container
- [ ] Remove the complicated styling and settings
- [ ] Create a simple, large container with minimal styling
- [ ] Let the chart naturally fill the container

### Step 2: Simplify chart initialization
- [ ] Remove any complicated canvas sizing code
- [ ] Allow Chart.js to handle sizing with its defaults
- [ ] Keep only essential Chart.js configuration options

### Step 3: Test the changes
- [ ] Verify that the chart appears at an appropriate size
- [ ] Confirm that text scaling is reasonable

## Files to Modify
- `app/views/agency/outcomes/view_outcome.php`
