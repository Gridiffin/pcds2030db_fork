# PCDS2030 Dashboard Missing ROOT_PATH and Function Fixes

## Issues to Fix

1. **ROOT_PATH Constant Errors**
   - [x] Fix undefined ROOT_PATH constant in `view_metric.php` (line 9)
   - [x] Fix undefined ROOT_PATH constant in `edit_metric.php` (line 9) 
   - [x] Fix undefined ROOT_PATH constant in `create_metric.php` (line 10)
   - [x] Fix undefined ROOT_PATH constant in `create_program.php` (line 9)

2. **Missing Function Errors**
   - [x] Fix undefined function `get_program_details()` in `program_details.php` (line 42)

3. **404 Error**
   - [x] Fix 404 error when clicking delete button on program page

## Implementation Plan

### 1. Fix ROOT_PATH Constant Errors
- [x] Found that PROJECT_ROOT_PATH was defined correctly but ROOT_PATH was being used in one place
- [x] Updated references from ROOT_PATH to PROJECT_ROOT_PATH in dashboard_header.php includes

### 2. Fix Missing Function
- [x] Verified `get_program_details()` exists in agencies/programs.php  
- [x] The function was available once the programs.php file was properly included

### 3. Fix Delete Button 404 Error
- [x] Identified incorrect use of view_url() function with placeholder variable
- [x] Fixed view_url() call by replacing '$ViewType' with 'agency'

## Implementation Details

### 1. ROOT_PATH Constant Fixes

In each file, we replaced:
```php
require_once ROOT_PATH . 'app/lib/dashboard_header.php';
```

With:
```php
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
```

This ensures that the correct path constant is used consistently throughout the files.

### 2. Missing Function Fix

The `get_program_details()` function exists in `app/lib/agencies/programs.php` which is included correctly via `agencies/index.php`. No direct fix was needed as this was resolved by ensuring all agency lib files are properly included.

### 3. Delete Button 404 Fix

Updated the view_programs.php file's delete form action from:
```php
<form action="<?php echo view_url('$ViewType', 'delete_program.php'); ?>" method="post" id="delete-program-form">
```

To:
```php
<form action="<?php echo view_url('agency', 'delete_program.php'); ?>" method="post" id="delete-program-form">
```

This ensures the correct URL is generated for the delete action, pointing to the agency/delete_program.php file.
