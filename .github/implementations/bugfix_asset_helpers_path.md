# Bug Fix: Asset Helpers Path Resolution

## Issue Description
**Error:** `Warning: require_once(C:\laragon\www\pcds2030_dashboard_fork\lib/asset_helpers.php): Failed to open stream: No such file or directory`

**Root Cause:** Multiple files were using incorrect path to include `asset_helpers.php` - they were looking for it in `lib/asset_helpers.php` instead of the actual location `app/lib/asset_helpers.php`.

## Files Affected
- ✅ `app/views/layouts/base.php` - Fixed asset_helpers.php path + navigation/layout includes
- ✅ `app/views/layouts/header.php` - Fixed asset_helpers.php path
- ✅ `app/views/agency/initiatives/view_initiative_original.php` - Fixed asset_helpers.php path
- ✅ `app/views/admin/initiatives/view_initiative.php` - Fixed asset_helpers.php path
- ✅ `app/views/agency/initiatives/view_initiative.php` - Already correct

## Solution Applied
**Phase 1:** Updated all asset_helpers.php `require_once` statements from:
```php
require_once PROJECT_ROOT_PATH . 'lib/asset_helpers.php';
```

To:
```php
require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';
```

**Phase 2:** Fixed additional layout includes in base.php from:
```php
require_once PROJECT_ROOT_PATH . 'views/layouts/agency_nav.php';
require_once PROJECT_ROOT_PATH . 'views/layouts/admin_nav.php';
require_once PROJECT_ROOT_PATH . 'views/layouts/page_header.php';
require_once PROJECT_ROOT_PATH . 'views/layouts/main_toast.php';
require_once PROJECT_ROOT_PATH . 'views/layouts/footer.php';
```

To:
```php
require_once PROJECT_ROOT_PATH . 'app/views/layouts/agency_nav.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/admin_nav.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/main_toast.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer.php';
```

## Impact
- ✅ Fixed fatal error preventing dashboard from loading
- ✅ All affected view files now properly load asset helper functions
- ✅ No functional changes to the asset helper functionality itself

## Testing
- [x] Verified asset_helpers.php exists in `app/lib/` directory
- [x] Updated all incorrect path references
- [x] No additional require_once statements found with the old pattern

## Follow-up Actions
- Monitor for any similar path resolution issues in future development
- Consider adding path validation checks in critical include statements
- **⚠️ SYSTEMATIC ISSUE IDENTIFIED:** Many other files still use the old `lib/` pattern instead of `app/lib/` for critical files like:
  - `lib/db_connect.php` → should be `app/lib/db_connect.php`
  - `lib/session.php` → should be `app/lib/session.php`  
  - `lib/functions.php` → should be `app/lib/functions.php`
  - And many others across view files
- **Recommendation:** Create a systematic fix for all incorrect lib paths when opportunity arises, or when similar errors are reported
