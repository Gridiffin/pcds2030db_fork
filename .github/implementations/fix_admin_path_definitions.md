# Fix Admin Path Definitions

## Problem
The moved admin files are defining their own `PROJECT_ROOT_PATH` instead of using the standardized `ROOT_PATH` that's already defined in `config.php`. This causes:
- Path calculation errors (duplicate `app/app/` paths)
- Inconsistency across the codebase
- Redundant path definition code

## Root Cause
When files were moved to subdirectories, `PROJECT_ROOT_PATH` was added with incorrect `dirname()` calculations, but `config.php` already provides `ROOT_PATH` for this purpose.

## Solution
Remove custom `PROJECT_ROOT_PATH` definitions and use the standard `ROOT_PATH` from config.php.

## Tasks

### 1. Analysis
- [x] Check all moved admin files for custom `PROJECT_ROOT_PATH` definitions
- [x] Verify how `ROOT_PATH` is supposed to work in the project
- [x] Identify the correct pattern used by working files

### 2. Fix Path Definitions
- [x] Remove `PROJECT_ROOT_PATH` definition from `dashboard/dashboard.php`
- [ ] Remove `PROJECT_ROOT_PATH` definition from other affected admin files
- [ ] Update require_once statements to use `ROOT_PATH` instead
- [ ] Ensure consistency with existing working files

### 3. Verification
- [ ] Test that all admin files load without path errors
- [ ] Verify navigation works correctly
- [ ] Check that all includes resolve properly

## Best Practice
Use the standardized `ROOT_PATH` defined in config.php rather than creating custom path definitions in individual files.
