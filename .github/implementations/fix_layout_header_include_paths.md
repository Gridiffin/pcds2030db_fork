# Fix Layout Header Include Path Issue

## Problem Description
The manage_outcomes.php page is throwing a fatal error because it cannot find the header.php layout file:
```
Warning: require_once(../layouts/header.php): Failed to open stream: No such file or directory
Fatal error: Failed opening required '../layouts/header.php'
```

## Root Cause Analysis
- [ ] The relative path `../layouts/header.php` is incorrect for the current file location
- [ ] File is located at: `app/views/admin/outcomes/manage_outcomes.php`
- [ ] Header file is located at: `app/views/layouts/header.php`
- [ ] Current path calculation is going up one level instead of two

## Solution Steps

### Step 1: Analyze Current File Structure
- [x] Verify the actual location of layout files
- [x] Check how other similar admin pages handle layout includes
- [x] Understand the correct relative path needed

### Step 2: Fix Include Paths
- [x] Update header.php include path from `../layouts/header.php` to `PROJECT_ROOT_PATH . 'app/views/layouts/header.php'`
- [x] Update admin_nav.php include path from `../layouts/admin_nav.php` to `PROJECT_ROOT_PATH . 'app/views/layouts/admin_nav.php'`
- [x] Ensure consistency with other admin outcome pages (using PROJECT_ROOT_PATH approach)

### Step 3: Validate Fix
- [x] Test PHP syntax validation
- [x] Test page loading in browser
- [x] Verify all layout elements render correctly

### Step 4: Code Review and Optimization
- [x] Check for similar issues in other admin outcome pages
- [x] Ensure consistency across all admin pages (using PROJECT_ROOT_PATH approach)
- [x] Document the correct pattern for future reference

## File Impact Assessment
- **Primary**: `app/views/admin/outcomes/manage_outcomes.php` ✅ **FIXED**
- **Reference**: Other admin outcome pages (already using correct PROJECT_ROOT_PATH pattern)
- **Layouts**: `app/views/layouts/header.php`, `app/views/layouts/admin_nav.php` ✅ **VERIFIED**

## Testing Checklist
- [x] PHP syntax check passes
- [x] Page loads without fatal errors
- [x] Header renders correctly
- [x] Navigation menu displays properly
- [x] All CSS and JS assets load correctly

## Resolution Summary
**ISSUE RESOLVED** ✅

**Root Cause:** Incorrect relative path `../layouts/header.php` in manage_outcomes.php
**Solution:** Updated to use `PROJECT_ROOT_PATH . 'app/views/layouts/header.php'` for consistency with other admin outcome pages
**Pattern Established:** All admin pages should use PROJECT_ROOT_PATH for layout includes to ensure portability across different hosting environments (especially important for cPanel deployment)

## Best Practice for Future Development
- Always use `PROJECT_ROOT_PATH . 'app/views/layouts/header.php'` for layout includes
- Avoid relative paths like `../layouts/` or `../../layouts/` which are fragile
- Follow the pattern established in other admin outcome files (`view_outcome.php`, `edit_outcome.php`)
