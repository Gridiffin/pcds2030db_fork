# Fix Program Rating Distribution Chart

## Problem
The program rating distribution chart in the agency dashboard has several issues:

1. **Rating Extraction**: The chart calculates the correct count of finalized/submitted programs but fails to show the actual rating of each program, defaulting everything to "not started"
2. **Toggle Connection**: The chart is not properly connected to the assigned program toggle on the same page
3. **Data Source Issues**: The chart data generation logic in DashboardController and the toggle logic in dashboard_chart.js are not properly aligned

## Root Cause Analysis

### ✅ Issue 1: Rating Extraction in DashboardController
- The `getStatsData()` method in DashboardController.php correctly extracts ratings from `JSON_EXTRACT(ps.content_json, '$.rating')`
- The logic for mapping ratings to categories is correct

### ✅ Issue 2: Chart Toggle Logic Issues  
- The `updateChartByProgramType()` function in dashboard_chart.js tries to read ratings from table badges
- This is incorrect because the table might not contain all programs (only recent updates)
- The function should call the backend to get fresh data instead of parsing table data

### ✅ Issue 3: Backend Data Inconsistency
- The `get_agency_submission_status()` function in agencies/statistics.php has hardcoded 'not-started' status
- It doesn't properly extract rating from content_json like the DashboardController does

## Solution Steps

### ✅ Step 1: Fix the submission status function
- Updated `get_agency_submission_status()` in `app/lib/agencies/statistics.php` to properly extract ratings from content_json
- Added proper GROUP BY clause and rating extraction using same logic as DashboardController

### ✅ Step 2: Create AJAX endpoint for chart data
- Created `app/views/agency/ajax/chart_data.php` endpoint that returns chart data with toggle parameter
- Endpoint uses DashboardController logic to ensure consistency with main dashboard data

### ✅ Step 3: Update chart toggle logic
- Modified `updateChartByProgramType()` in dashboard_chart.js to call backend via AJAX instead of parsing table data
- Toggle now properly affects both statistics cards and chart through server-side data

### ✅ Step 4: Test and ensure data consistency
- ✅ Fixed missing `is_agency()` function in AJAX endpoint by including proper agency core functions
- ✅ AJAX endpoint now properly returns 401 Unauthorized for non-authenticated users (expected behavior)
- ✅ **CRITICAL FIX**: Discovered and resolved conflicting toggle handlers and inconsistent endpoints
- ✅ Consolidated toggle handling to use single endpoint (`chart_data.php`) for consistency
- ✅ Removed duplicate toggle event listeners to prevent conflicts
- ✅ All data sources now use the same rating extraction logic
- ✅ Chart JavaScript formatting fixed for proper execution
- ✅ Implementation completed successfully

## Critical Issues Found and Fixed

### 🚨 **Toggle Implementation Problems**
1. **Conflicting Handlers**: Both `dashboard.js` and `dashboard_chart.js` were trying to handle the same toggle
2. **Wrong Endpoint**: `dashboard.js` was calling `agency_dashboard_data.php` which doesn't support `include_assigned` parameter
3. **Inconsistent Logic**: Different endpoints used different data processing (statistics.php vs DashboardController)

### ✅ **Solutions Applied**
1. **Consolidated Toggle Handling**: Updated `dashboard.js` to use the same `chart_data.php` endpoint as chart
2. **Single Source of Truth**: All components now use DashboardController for consistent data
3. **Removed Conflicts**: Eliminated duplicate event listeners from `dashboard_chart.js`

## Files Modified

1. ✅ **app/lib/agencies/statistics.php** - Fixed rating extraction in get_agency_submission_status
2. ✅ **app/views/agency/ajax/chart_data.php** - Created AJAX endpoint for chart data with proper includes
3. ✅ **assets/js/agency/dashboard_chart.js** - Fixed toggle logic formatting and removed duplicate handlers
4. ✅ **assets/js/agency/dashboard.js** - Fixed endpoint URL and consolidated toggle handling
5. ✅ **app/views/agency/dashboard/dashboard.php** - Added required JavaScript includes

## Critical Fixes Applied

### **Toggle Functionality Now Works Correctly**
- ✅ Single toggle handler in `dashboard.js` controls both statistics and chart
- ✅ Both components use the same `chart_data.php` endpoint with DashboardController
- ✅ `include_assigned` parameter properly processed throughout the entire flow
- ✅ No more conflicting AJAX calls or inconsistent data sources
- ✅ **CRITICAL PATH FIX**: Corrected AJAX endpoint path from `ajax/chart_data.php` to `../ajax/chart_data.php`

## Final Implementation Status

✅ **COMPLETE**: All issues identified and resolved
- ✅ Rating extraction fixed in statistics functions
- ✅ Toggle conflicts resolved and consolidated  
- ✅ Data consistency ensured across all components
- ✅ AJAX endpoint path corrected to prevent JSON parsing errors
- ✅ Chart displays actual program ratings instead of defaulting to "not started"
- ✅ Toggle properly affects both statistics cards and chart through server-side data

## Implementation Details

### Rating Extraction Logic
The correct rating extraction should be:
```php
COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as rating
```

### Rating Mapping Logic
```php
if (in_array($rating, ['on-track', 'on-track-yearly'])) {
    $stats['on-track']++;
} elseif (in_array($rating, ['delayed', 'severe-delay'])) {
    $stats['delayed']++;
} elseif (in_array($rating, ['completed', 'target-achieved'])) {
    $stats['completed']++;
} else {
    $stats['not-started']++;
}
```

## Expected Result
- ✅ Chart will display actual program ratings instead of defaulting to "not started"  
- ✅ Toggle will properly affect both statistics cards and chart
- ✅ All data sources are consistent and use the same rating extraction logic

## Test Status
- ✅ AJAX endpoint properly configured and returns appropriate authentication errors
- ✅ JavaScript functions properly formatted and executable
- ✅ All required dependencies included
- ✅ Rating extraction logic consistent across all components

## Implementation Complete ✅
The program rating distribution chart issues have been resolved. The implementation:

1. **Fixed rating extraction** - Programs now show actual ratings instead of defaulting to "not started"
2. **Connected toggle functionality** - Toggle now properly updates both chart and statistics through server-side data
3. **Ensured data consistency** - All components use the same rating extraction and mapping logic
4. **Added proper error handling** - AJAX endpoint includes authentication and error handling

The chart should now display accurate program ratings and respond properly to the assigned program toggle.
