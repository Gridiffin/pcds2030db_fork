# Fix Remaining Path Issues in create_outcomes_detail.php

## Problem
Even after fixing the `PROJECT_ROOT_PATH` definition, there are still relative path issues in `create_outcomes_detail.php`:
- Line 20: `require_once '../layouts/header.php';`
- Line 21: `require_once '../layouts/agency_nav.php';`

These relative paths are incorrect from the current file location.

## Root Cause Analysis
Current file location: `d:\laragon\www\pcds2030_dashboard\app\views\agency\outcomes\create_outcomes_detail.php`

Trying to access: `../layouts/header.php`
- This resolves to: `d:\laragon\www\pcds2030_dashboard\app\views\agency\layouts\header.php` ❌

But the actual file location is: `d:\laragon\www\pcds2030_dashboard\app\views\layouts\header.php` ✅

## Solution Steps
- [x] Identify the incorrect relative paths
- [x] Update lines 20-21 to use PROJECT_ROOT_PATH
- [x] Test the fix
- [x] Verify the page loads correctly

## Technical Fix Applied ✅
Changed from relative paths to absolute paths using PROJECT_ROOT_PATH:

**Before:**
```php
require_once '../layouts/header.php';
require_once '../layouts/agency_nav.php';
```

**After:**
```php
require_once PROJECT_ROOT_PATH . 'app/views/layouts/header.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/agency_nav.php';
```

## Files Fixed ✅
- ✅ `d:\laragon\www\pcds2030_dashboard\app\views\agency\outcomes\create_outcomes_detail.php` (lines 20-21)

## Expected Outcome ✅
- ✅ Page loads without "Failed to open stream" errors
- ✅ Header and navigation components load correctly

**STATUS: COMPLETED**
