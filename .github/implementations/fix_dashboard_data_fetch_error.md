# Fix Dashboard Data Fetch Error

## Problem
The dashboard is showing an error when trying to fetch data from the server:
```
Error fetching dashboard data: Error: HTTP error! Status: 404
fetchDashboardData http://localhost/pcds2030_dashboard/assets/js/agency/dashboard.js:91
```

A 404 error indicates that the server cannot find the requested resource. In this case, the JavaScript is trying to fetch data from `../app/ajax/dashboard_data.php`, but this file either doesn't exist or is not accessible at that path.

## Analysis Steps
- [x] Examine the current fetch URL in dashboard.js
- [x] Check if the dashboard_data.php file exists in the expected location
- [x] Verify correct path to the AJAX endpoint
- [x] Determine if the correct endpoint is agency_dashboard_data.php instead
- [x] Fix the path in the JavaScript code
- [ ] Test the fix

## Solution Plan
1. Check if dashboard_data.php exists
2. If not, determine the correct endpoint file (likely agency_dashboard_data.php based on file structure)
3. Update the fetch URL in dashboard.js to point to the correct endpoint
4. Test the fix to ensure data is loading properly

## Implementation Details
1. Found that both `dashboard_data.php` and `agency_dashboard_data.php` exist in the app/ajax directory
2. Based on the file structure and naming conventions, determined that `agency_dashboard_data.php` is more appropriate for the agency dashboard
3. Updated the fetch URL in `dashboard.js` from:
   ```javascript
   fetch(`../app/ajax/dashboard_data.php`, {
   ```
   to:
   ```javascript
   fetch(`../app/ajax/agency_dashboard_data.php`, {
   ```
4. This should resolve the 404 error and allow the dashboard to properly load data
