# Revert API Naming Changes - metrics_details to outcomes_details

## Problem
User has reverted all changes and wants to revert the API naming from `outcomes_details` back to `metrics_details` to isolate the chart display issues.

## Solution
Revert the API naming changes systematically to restore the original working state.

## Implementation Steps

### 1. 🔄 Analysis
- [ ] Check current state of files after user reversion
- [ ] Identify what needs to be changed back to `metrics_details`
- [ ] Document the changes needed

### 2. 🔄 Backend API Changes  
- [ ] Revert `outcomes_details` back to `metrics_details` in report_data.php
- [ ] Update any related API responses
- [ ] Ensure consistency across all endpoints

### 3. 🔄 Frontend JavaScript Changes
- [ ] Update chart functions to expect `metrics_details` instead of `outcomes_details`
- [ ] Modify data access patterns in report modules
- [ ] Update any fallback logic

### 4. 🧪 Testing
- [ ] Generate test report to verify charts work
- [ ] Monitor console for errors
- [ ] Confirm data flows correctly

### 5. 📝 Documentation
- [ ] Update implementation tracking
- [ ] Document current working state

## Files to Check/Modify
- `/app/api/report_data.php` - Main API endpoint
- `/assets/js/report-modules/report-slide-styler.js` - Chart functions
- `/assets/js/report-modules/report-slide-populator.js` - Data handling

## Expected Outcome
Restore the original working API naming structure to isolate chart issues from naming changes.
