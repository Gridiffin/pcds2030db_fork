# Debug Admin Outcomes Page Loading Issue

**Date:** 2025-05-26  
**Status:** ✅ **RESOLVED**

**Problem:** The admin outcomes page (`app/views/admin/outcomes/manage_outcomes.php`) was not loading. No errors were reported in PHP logs or the browser console. Network tab showed only URL helpers and Chart.js loading.

**Root Cause:** Incorrect understanding of ROOT_PATH definition - ROOT_PATH points to project root (`pcds2030_dashboard/`), not to `app/` directory.

## Final Resolution:

**Issue Identified:** ROOT_PATH is defined in `config.php` as `dirname(dirname(dirname(__FILE__)))` which resolves to the project root, not the app directory. Therefore, lib files must be referenced as `ROOT_PATH . 'app/lib/...'` not `ROOT_PATH . 'lib/...'`.

**Path Analysis:**
- config.php location: `d:\laragon\www\pcds2030_dashboard\app\config\config.php`
- ROOT_PATH = `dirname(dirname(dirname(__FILE__)))` = `d:\laragon\www\pcds2030_dashboard`
- lib files location: `d:\laragon\www\pcds2030_dashboard\app\lib\`
- Correct reference: `ROOT_PATH . 'app/lib/db_connect.php'`

**Solution Applied:**
1. ✅ **Corrected all outcome files to use proper ROOT_PATH references**
   - `manage_outcomes.php`: Fixed to use `ROOT_PATH . 'app/lib/...'`
   - `edit_outcome.php`: Fixed to use `ROOT_PATH . 'app/lib/...'` and all layout includes
   - `view_outcome.php`: Fixed to use `ROOT_PATH . 'app/lib/...'` and corrected PROJECT_ROOT_PATH to ROOT_PATH for all includes
   - `delete_outcome.php`: Fixed to use `ROOT_PATH . 'app/lib/...'`
   - `unsubmit_outcome.php`: Fixed to use `ROOT_PATH . 'app/lib/...'`

2. ✅ **Fixed all PROJECT_ROOT_PATH references**
   - Replaced all `PROJECT_ROOT_PATH` occurrences with `ROOT_PATH` in outcome files
   - Fixed layout includes (header.php, admin_nav.php, footer.php) 
   - Fixed library includes (dashboard_header.php)

3. ✅ **Verified path consistency with working admin files**
   - Confirmed `admin/dashboard/dashboard.php` uses `ROOT_PATH . 'app/lib/...'` pattern

## Files Modified:

- ✅ `app/views/admin/outcomes/manage_outcomes.php` - **RESOLVED** (correct ROOT_PATH usage)
- ✅ `app/views/admin/outcomes/edit_outcome.php` - **RESOLVED** (correct ROOT_PATH usage)
- ✅ `app/views/admin/outcomes/view_outcome.php` - **RESOLVED** (correct ROOT_PATH usage, fixed PROJECT_ROOT_PATH)
- ✅ `app/views/admin/outcomes/delete_outcome.php` - **RESOLVED** (correct ROOT_PATH usage)
- ✅ `app/views/admin/outcomes/unsubmit_outcome.php` - **RESOLVED** (correct ROOT_PATH usage)

**Result:** All admin outcome pages should now load correctly with proper ROOT_PATH resolution to include files from `app/lib/` directory.
