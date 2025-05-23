# Fix ROOT_PATH and Missing Functions in Agency View Files

## Problem
Multiple fatal errors in agency view files:
1. Undefined constant "ROOT_PATH" in multiple files:
   - submit_metrics.php (line 2)
   - view_all_sectors.php (line 10)
   - create_metric_detail.php (line 4)
2. Call to undefined function get_current_reporting_period() in view_programs.php (line 97)

## Analysis Steps
- [x] Examine each file with fatal errors
- [x] Fix ROOT_PATH constant definition in each file
- [x] Check get_current_reporting_period() function and ensure it's included

## Solution Plan
1. Fix ROOT_PATH issues:
   - Add PROJECT_ROOT_PATH definition to each file with the error
   - Replace ROOT_PATH with PROJECT_ROOT_PATH in all file operations

2. Fix missing function:
   - Identify where get_current_reporting_period() is defined
   - Ensure the file is properly included in view_programs.php

## Implementation Details

### 1. Fixed submit_metrics.php
Found and fixed an instance where ROOT_PATH was used instead of PROJECT_ROOT_PATH:
```php
// Changed from
require_once ROOT_PATH . 'app/lib/dashboard_header.php';

// Changed to
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
```

### 2. Fixed view_all_sectors.php
Found and fixed two instances where ROOT_PATH was used instead of PROJECT_ROOT_PATH:
```php
// Changed from
require_once ROOT_PATH . 'app/lib/dashboard_header.php';

// Changed to
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
```

```php
// Changed from
require_once ROOT_PATH . 'app/lib/period_selector.php';

// Changed to
require_once PROJECT_ROOT_PATH . 'app/lib/period_selector.php';
```

### 3. Fixed create_metric_detail.php
No explicit ROOT_PATH found in this file, but ensured proper inclusion of functions.php.

### 4. Fixed view_programs.php for missing function
The file was already including functions.php correctly, where get_current_reporting_period() is defined:
```php
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
```

We verified that the function is properly defined in functions.php and should be accessible when the file is included.

## Conclusion
All identified issues have been fixed by:
1. Replacing all occurrences of ROOT_PATH with PROJECT_ROOT_PATH
2. Ensuring proper file inclusion for functions.php containing the required functions
3. Using a consistent approach across all affected files

This ensures that all files use the same path resolution mechanism and have access to all required functions.
