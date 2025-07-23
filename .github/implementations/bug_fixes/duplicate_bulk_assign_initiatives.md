# Bug Fix: Duplicate Bulk Assign Initiatives Button & CSS Bundle Issues

## Issue Description

**Issue 1: Duplicate Button**
There are two "Bulk Assign Initiatives" buttons appearing on the admin programs page:

1. Header Button: In `programs.php` header_config actions (modern approach)
2. Content Button: Directly in `programs_content.php` (legacy approach)

**Issue 2: Missing CSS Styling**
Admin pages are trying to load non-existent CSS bundles:

- `admin-programs.bundle.css` (doesn't exist)
- `admin-common.bundle.css` (doesn't exist)

This causes the green header theme and proper styling to not load.

## Root Cause Analysis

- The content button in `programs_content.php` is a legacy implementation
- The header actions system was implemented later as a standardized approach
- Both buttons remained, creating visual redundancy

## Solution Plan

- [x] **THINK**: Identified duplicate buttons in header vs content area
- [x] **REASON**: Header actions follow modern architectural patterns used across admin pages
- [x] **SUGGEST**: Remove content button, keep header action for consistency
- [x] **ACT**: Remove duplicate button from programs_content.php
- [x] **VERIFY**: Test the page to ensure single button remains functional
- [x] **BONUS FIX**: Fixed CSS bundle loading issue

## Files to Modify

- `app/views/admin/programs/partials/programs_content.php` - Remove duplicate button
- `app/views/admin/programs/programs.php` - Fix CSS bundle loading
- `app/views/admin/initiatives/manage_initiatives.php` - Fix CSS bundle loading

## Implementation Details

- ✅ Removed lines 11-14 in programs_content.php (the duplicate standalone button)
- ✅ Kept header action in programs.php unchanged for consistency
- ✅ Maintained proper functionality through header action button

## Changes Made

**File: `app/views/admin/programs/partials/programs_content.php`**

- Removed the duplicate "Bulk Assign Initiatives" button div (lines 11-14)
- Kept toast notification functionality intact
- Preserved all table includes and JavaScript variables

**File: `app/views/admin/programs/programs.php`**

- Fixed CSS bundle from `admin-programs` to `programs` to resolve missing styles
- This ensures the green header and proper styling loads correctly

**File: `app/views/admin/initiatives/manage_initiatives.php`**

- Fixed CSS bundle from `admin-common` to `outcomes` to resolve missing styles
- This ensures the green header theme loads properly for admin initiatives page

## Testing Checklist

- [x] Verify only one "Bulk Assign Initiatives" button appears
- [x] Confirm button functionality works from header location
- [x] Check page header styling matches outcomes page (green theme)
- [ ] Check responsive behavior on mobile devices
