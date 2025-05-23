# Error Fixes Implementation Plan

## Issues Fixed

1. **Missing functions:**
   - [x] `get_agency_sector_metrics()` in submit_metrics.php (line 33)
   - [x] `get_draft_metric()` in submit_metrics.php
   - [x] `get_all_sectors_programs()` in view_all_sectors.php (line 56)

2. **ROOT_PATH constant undefined:**
   - [x] Fix in update_program.php (line 9)
   - [x] Fix in program_details.php (line 9)
   - [x] Fix in delete_program.php (line 9)

3. **404 Error:**
   - [x] Fixed path for delete_program.php by updating the ROOT_PATH definition

## Implementation Details

### 1. Created Missing Functions
We created the necessary functions by:

1. Creating a new `metrics.php` file in the app/lib/agencies directory that contains:
   - `get_agency_sector_metrics()` function
   - `get_draft_metric()` function

2. Creating a new `outcomes.php` file in the app/lib/agencies directory that contains:
   - `get_agency_sector_outcomes()` function
   - `get_draft_outcome()` function

3. Adding the `get_all_sectors_programs()` function to the statistics.php file

### 2. Fixed ROOT_PATH Constant
We added PROJECT_ROOT_PATH definition to affected files:
- update_program.php
- program_details.php
- delete_program.php

For each file, we added:
```php
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
```

And updated the require statements to use PROJECT_ROOT_PATH:
```php
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
```

### 3. Fixed 404 Error with delete_program.php
The delete_program.php file was present in the correct location but had the ROOT_PATH constant issue. By fixing the constant definition, the file can now be correctly included and accessed.
