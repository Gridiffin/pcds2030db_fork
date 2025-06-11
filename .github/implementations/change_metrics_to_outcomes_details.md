# Change metrics_details back to outcomes_details

## Problem
User has reverted all changes and now wants to change `metrics_details` back to `outcomes_details` in the API to maintain consistency.

## Solution
Update the API naming from `metrics_details` to `outcomes_details` systematically.

## Implementation Steps

### 1. ✅ Analysis (COMPLETED)
- [x] User has reverted all previous changes
- [x] Need to change `metrics_details` back to `outcomes_details` 
- [x] Check current state of files

### 2. ✅ Backend API Changes (COMPLETED)
- [x] Update `metrics_details` to `outcomes_details` in report_data.php
- [x] Update variable names and JSON response keys
- [x] Ensure consistency across the API

### 3. ✅ Frontend JavaScript Changes (COMPLETED)
- [x] Update chart functions to expect `outcomes_details` instead of `metrics_details`
- [x] Modify data access patterns in report modules
- [x] Update any fallback logic

### 4. ⏳ Testing (READY FOR USER)
- [ ] Generate test report to verify charts work
- [ ] Monitor console for errors
- [ ] Confirm data flows correctly

### 5. ✅ Documentation (COMPLETED)
- [x] Update implementation tracking

## Files to Modify
- `/app/api/report_data.php` - Main API endpoint
- `/assets/js/report-modules/report-slide-styler.js` - Chart functions
- `/assets/js/report-modules/report-slide-populator.js` - Data handling

## Expected Outcome
Change the API to use `outcomes_details` consistently.
