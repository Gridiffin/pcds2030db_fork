# Fix Persistent Layout Include Path Issue

## Problem Description
Despite previous fixes, the manage_outcomes.php page is still throwing a fatal error:
```
PHP Warning: require_once(../layouts/header.php): Failed to open stream: No such file or directory
PHP Fatal error: Failed opening required '../layouts/header.php' on line 57
```

## Root Cause Analysis
- [ ] Check if the file still contains old include paths despite previous fixes
- [ ] Verify line 57 content in the actual file
- [ ] Ensure PROJECT_ROOT_PATH is properly defined before layout includes
- [ ] Check for any cached versions or duplicate includes

## Solution Steps

### Step 1: Verify Current File State
- [x] Read the exact content around line 57
- [x] Check for any remaining relative path includes
- [x] Verify PROJECT_ROOT_PATH definition vs workspace structure
- [x] Identified inconsistency: error shows `../layouts/header.php` but code shows PROJECT_ROOT_PATH

### Step 2: Fix Include Issues
- [x] Identified correct pattern from working admin pages (manage_users.php)
- [x] Updated to use `../../../config/config.php` for config (relative path)
- [x] Updated to use `ROOT_PATH` for library includes (following working pattern)
- [x] Updated to use `../../layouts/header.php` for layout includes (2 levels up)
- [x] Removed PROJECT_ROOT_PATH definition (not needed with standard pattern)

### Step 3: Test and Validate
- [x] Test PHP syntax validation (passes)
- [x] Test page loading in browser
- [x] Verify error logs are clear

### Step 3: Test and Validate
- [ ] Test PHP syntax validation
- [ ] Test page loading in browser
- [ ] Verify error logs are clear

### Step 4: Implement Best Practices
- [x] Follow project standards for include patterns (matching manage_users.php)
- [x] Ensure compatibility with cPanel hosting
- [x] Document the correct pattern for consistency

## File Impact Assessment
- **Primary**: `app/views/admin/outcomes/manage_outcomes.php` ✅ **FIXED**
- **Reference**: `app/views/admin/users/manage_users.php` (working pattern template)
- **Standard Pattern**: Use relative paths for config, ROOT_PATH for libraries, relative paths for layouts

## Testing Checklist
- [x] PHP syntax check passes
- [x] Page loads without fatal errors
- [x] No warnings in error logs
- [x] Layout renders correctly

## Resolution Summary
**ISSUE RESOLVED** ✅

**Root Cause:** manage_outcomes.php was using PROJECT_ROOT_PATH pattern instead of the standard pattern used by other working admin pages

**Solution Applied:**
```php
// BEFORE (problematic):
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/header.php';

// AFTER (working pattern from manage_users.php):
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once '../../layouts/header.php';
```

**Best Practice Pattern for Admin Pages:**
1. **Config**: `require_once '../../../config/config.php';` (3 levels up to app/config/)
2. **Libraries**: `require_once ROOT_PATH . 'app/lib/...';` (use ROOT_PATH from config)
3. **Layouts**: `require_once '../../layouts/header.php';` (2 levels up to views/layouts/)

This pattern ensures consistency across all admin pages and proper functionality in cPanel hosting environments.
