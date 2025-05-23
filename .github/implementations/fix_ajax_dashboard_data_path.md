# AJAX Dashboard Data Path Issue Fix

## Problem Description
The agency dashboard is encountering a JSON parsing error:
```
Error fetching dashboard data: SyntaxError: JSON.parse: unexpected character at line 1 column 1 of the JSON data
```

This error occurs because the AJAX endpoint (`app/ajax/dashboard_data.php`) is trying to include the DashboardController from an incorrect path:
```php
require_once '../lib/DashboardController.php';
```

However, based on the project structure, the DashboardController is located in `app/controllers/DashboardController.php`.

## Solution Steps

- [x] Check the dashboard.js file to understand how AJAX requests are made
- [x] Confirm the ajaxUrl function in url_helpers.js is properly constructing the URL
- [x] Examine the dashboard_data.php AJAX handler file
- [x] Identify that the DashboardController.php is being included from the wrong path
- [x] Update the require_once path to point to the correct location
- [x] Verify that the correct DashboardController class is being referenced

## Implementation Details

1. Update app/ajax/dashboard_data.php to:
   - Change `require_once '../lib/DashboardController.php';` to `require_once '../controllers/DashboardController.php';`
   - Ensure all required dependencies are properly included

## Testing

The fix should:
- Resolve the JSON parsing error in the agency dashboard
- Allow dashboard data to load correctly
- Maintain all dashboard functionality
