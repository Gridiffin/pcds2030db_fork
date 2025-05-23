# Fix ROOT_PATH constant error and missing styles

## Problem Analysis

1. **ROOT_PATH Constant Error**:
   - Error: `Undefined constant "ROOT_PATH"` in both:
     - `D:\laragon\www\pcds2030_dashboard\app\views\admin\dashboard.php`
     - `D:\laragon\www\pcds2030_dashboard\app\views\agency\dashboard.php`
   - Root cause: The dashboard files define `PROJECT_ROOT_PATH` but then try to use `ROOT_PATH` in the require statements.

2. **Missing Styles**:
   - Symptoms: Only HTML text showing, no CSS styling applied
   - CSS files are centralized in `base.css` and `main.css`, referenced in the header file
   - The header properly references the CSS files using `asset_url()` function

## Solution Steps

- [x] Fix the ROOT_PATH constant issue in both dashboard files:
  - Changed all occurrences of `ROOT_PATH` to `PROJECT_ROOT_PATH` in require statements
  - This aligns with how the constants are used in index.php

- [x] Verify the header.php file is properly included in both dashboard files
  - Found and fixed another instance of `ROOT_PATH` in both dashboard files where it was used to include dashboard_header.php
  - Found and fixed one more instance of `ROOT_PATH` in both dashboard files where it was used to include period_selector.php

- [x] Check if `asset_url()` function is correctly defined and functioning
  - The function is correctly defined in config.php
  - It uses APP_URL to generate correct URLs to assets

- [x] Inspect CSS references in the header to ensure paths are correct
  - main.css correctly imports all the necessary CSS files
  - base.css is also imported and provides core styling elements

- [x] Implementation complete! After applying these changes, the constant error should be resolved
- [ ] Test both admin and agency dashboards to verify styling is restored (to be completed by the user)

## Implementation

### Immediate Fixes Applied

1. Fixed **ROOT_PATH** constant issues in both dashboard files:
   - Changed `require_once ROOT_PATH . 'app/lib/db_connect.php';` to `require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';` and similar for other includes
   - Fixed `dashboard_header.php` inclusion in both files
   - Fixed `period_selector.php` inclusion in both files

2. Verified the CSS loading structure:
   - Confirmed that header.php is properly included in both dashboard files
   - Verified that asset_url() function works correctly to reference CSS files
   - Confirmed that main.css and base.css properly import all necessary CSS files

### Testing Instructions

To verify the fix has resolved the issues:

1. Try to login as an admin user and verify the dashboard loads correctly with styles
2. Try to login as an agency user and verify the dashboard loads correctly with styles 
3. Check if any console errors related to CSS files not loading are present

### Long-term Solution Recommendations

For better maintainability:

1. **Create a centralized bootstrap file**:
   - Create an `init.php` file at the project root that defines all necessary constants
   - Include this file at the beginning of every directly accessed PHP file
   - Example:
   ```php
   <?php
   // init.php
   define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
   define('ROOT_PATH', PROJECT_ROOT_PATH); // For backward compatibility
   require_once PROJECT_ROOT_PATH . 'app/config/config.php';
   ```

2. **Use a consistent routing system**:
   - Implement a front controller pattern where all requests pass through index.php
   - This would eliminate the need to define constants in each individual file
   - Would provide better URL structure and security
