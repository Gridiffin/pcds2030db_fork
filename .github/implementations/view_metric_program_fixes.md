# PCDS2030 Dashboard Error Fixes Implementation Plan

## Issues to Fix

1. **ROOT_PATH Constant Undefined Errors**
   - [ ] Fix in view_metric.php (line 9)
   - [ ] Fix in edit_metric.php (line 9)
   - [ ] Fix in create_metric.php (line 10)
   - [ ] Fix in create_program.php (line 9)

2. **404 Error on Delete Button**
   - [ ] Investigate and fix the delete program functionality

3. **Undefined Function Error**
   - [ ] Fix `Call to undefined function get_program_details()` in program_details.php (line 42)

## Implementation Steps

### 1. Fix ROOT_PATH Constant Issues
- [ ] Add PROJECT_ROOT_PATH definition to each affected file
- [ ] Replace ROOT_PATH with PROJECT_ROOT_PATH in includes

### 2. Fix 404 Error with Delete Button
- [ ] Find the correct file path for delete_program.php
- [ ] Update references to point to the correct path

### 3. Fix Undefined Function
- [ ] Locate or create the get_program_details() function
- [ ] Ensure it's included in program_details.php

## Implementation Details
We'll document all changes here as they're completed.
