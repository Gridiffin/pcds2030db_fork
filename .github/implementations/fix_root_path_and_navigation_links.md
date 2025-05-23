# Fix ROOT_PATH Constant and Navigation Links

## Problem
1. Fatal error in view_programs.php: `Uncaught Error: Undefined constant "ROOT_PATH"` on line 9
2. Agency navbar links are incorrect, pointing to "\views" instead of "\app\views"

## Analysis Steps
- [x] Examine the error in view_programs.php
- [x] Check how ROOT_PATH is defined in other files
- [x] Fix the ROOT_PATH constant definition in view_programs.php
- [x] Identify the agency navbar file
- [x] Fix the navigation links to use correct paths

## Solution Plan
1. Fix the ROOT_PATH constant in view_programs.php
   - Find how ROOT_PATH is defined in other working files
   - Add the proper definition to view_programs.php
   
2. Fix agency navigation links
   - Locate the agency navigation template file
   - Update links from "\views" to "\app\views"
   - Ensure consistency with the rest of the application

## Implementation Details

### 1. Fixing ROOT_PATH in view_programs.php

#### Issue
The view_programs.php file was trying to use the ROOT_PATH constant without defining it first, causing a fatal error.

#### Solution
1. Added PROJECT_ROOT_PATH definition consistent with other files:
```php
// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
```

2. Changed all occurrences from ROOT_PATH to PROJECT_ROOT_PATH in view_programs.php:
```php
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
```

3. Added ROOT_PATH definition to config.php to ensure it's available for all files:
```php
// Path definitions
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim(dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
```

### 2. Fixing Agency Navigation Links

#### Issue
The agency navigation menu had incorrect URLs with `/views/agency/` instead of `/app/views/agency/`, causing navigation failures.

#### Solution
Updated all navigation links in `app/views/layouts/agency_nav.php` from:
```php
href="<?php echo APP_URL; ?>/views/agency/dashboard.php"
```
to:
```php
href="<?php echo APP_URL; ?>/app/views/agency/dashboard.php"
```

Changed the following links:
1. Dashboard link
2. My Programs link
3. Outcomes link
4. Create Outcome Details link
5. All Sectors link
6. View all notifications link

## Conclusion

Both issues have been successfully fixed:

1. The ROOT_PATH constant error in view_programs.php was resolved by:
   - Adding the proper PROJECT_ROOT_PATH definition
   - Updating all require statements to use PROJECT_ROOT_PATH
   - Ensuring ROOT_PATH is defined in config.php for global availability

2. The navigation links in the agency navbar were fixed by:
   - Updating all URLs to use the correct path `/app/views/agency/` instead of `/views/agency/`
   - This ensures consistent navigation throughout the application

These changes improve the stability of the application by preventing fatal errors and ensuring users can navigate properly throughout the system.
