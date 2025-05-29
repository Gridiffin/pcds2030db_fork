# Agency Dashboard Restructuring Plan

## Overview
This plan outlines the step-by-step process to restructure the agency views to match the admin side's organized directory structure, starting with the dashboard.

## Current State Analysis

### ✅ Current Agency Dashboard Structure
- **Main File**: `/app/views/agency/dashboard.php` - Currently in root agency directory
- **AJAX File**: `/app/views/agency/ajax/dashboard_data.php` - Already in ajax subdirectory
- **Related Files**: Various dashboard-related components scattered in agency root

### 🎯 Target Admin Dashboard Structure
- **Main File**: `/app/views/admin/dashboard/dashboard.php` - Organized in dashboard subdirectory
- **AJAX File**: `/app/views/admin/ajax/admin_dashboard_data.php` - In ajax subdirectory

## Implementation Plan

### Phase 1: Dashboard Restructuring

#### 📋 Tasks for Dashboard Organization

- [ ] **Task 1.1**: Create `/app/views/agency/dashboard/` directory
- [ ] **Task 1.2**: Move `dashboard.php` to `/app/views/agency/dashboard/dashboard.php`
- [ ] **Task 1.3**: Update all include paths in the moved dashboard file
- [ ] **Task 1.4**: Track and update all redirection paths that reference the dashboard
- [ ] **Task 1.5**: Update navigation references
- [ ] **Task 1.6**: Test dashboard functionality after restructuring

#### 🔍 Redirection Paths Identified

**Incoming References to Agency Dashboard:**
1. **Navigation Links**:
   - `/app/views/layouts/agency_nav.php` (Line 70, 78) - Navigation brand and dashboard links
   
2. **Index/Entry Points**:
   - `/index.php` (Line 29) - Main entry point redirect
   
3. **Authentication Redirects**:
   - Various files redirect to dashboard after login/actions

**Outgoing References from Dashboard**:
1. **Include Paths** (need to be updated):
   - `../layouts/header.php` → `../../layouts/header.php`
   - `../layouts/agency_nav.php` → `../../layouts/agency_nav.php`
   - `../layouts/footer.php` → `../../layouts/footer.php`

2. **Asset References**:
   - Already using `APP_URL` and `asset_url()` functions - should work fine

3. **AJAX References**:
   - Dashboard uses AJAX calls to `/app/views/agency/ajax/dashboard_data.php` - will need relative path update

#### 🛠️ File Updates Required

**Files to Update After Moving Dashboard:**

1. **`/app/views/layouts/agency_nav.php`**:
   - Line 70: `href="<?php echo APP_URL; ?>/app/views/agency/dashboard.php"` → `dashboard/dashboard.php`
   - Line 78: `href="<?php echo APP_URL; ?>/app/views/agency/dashboard.php"` → `dashboard/dashboard.php`

2. **`/index.php`**:
   - Line 29: `$agency_dashboard = APP_URL . '/app/views/agency/dashboard.php';` → `dashboard/dashboard.php`

3. **Any authentication/redirect files** that reference the dashboard

4. **Dashboard file itself**:
   - Update all relative include paths
   - Update AJAX endpoint references

### Phase 1 Questions for Clarification

1. **Backward Compatibility**: Do you want to keep a redirect file at the old location (`dashboard.php`) that redirects to the new location (`dashboard/dashboard.php`) for backward compatibility?

2. **AJAX Structure**: Should we also reorganize the AJAX files to match admin structure (move dashboard_data.php to a dashboard-specific ajax folder)?

3. **Testing Approach**: After each file move, do you want to test immediately, or shall we complete the entire dashboard restructuring and then test?

4. **Error Handling**: How should we handle any hardcoded references we might miss? Should we add error logging or user-friendly error messages?

5. **Asset Dependencies**: The dashboard references several CSS/JS files. Should we verify all asset loading works correctly after the move?

## Implementation Decisions ✅

**Based on user feedback:**
1. ❌ No backward compatibility redirect needed
2. ✅ Yes, reorganize AJAX files to match admin structure  
3. ✅ Complete dashboard first, then test, then move to next files
4. ✅ Handle all operations in one batch
5. ✅ Use PROJECT_ROOT_PATH for consistency

## Current Priority

**HIGH PRIORITY PATHS TO TRACK:**
- Navigation menu links (agency_nav.php)
- Main entry point redirects (index.php)  
- Include statements within dashboard.php
- AJAX endpoint references

## Dashboard Restructuring - COMPLETED ✅

### Files Moved:
- `dashboard.php` → `/app/views/agency/dashboard/dashboard.php` ✅
- `agency_dashboard_data.php` → `/app/views/agency/dashboard/ajax/agency_dashboard_data.php` ✅

### Files Updated:
- `/login.php` - Updated redirect path ✅
- `/index.php` - Updated redirect path ✅ 
- `/app/views/layouts/agency_nav.php` - Updated navigation links ✅
- `/assets/js/period_selector.js` - Updated AJAX endpoint path ✅
- `/app/views/agency/dashboard/ajax/agency_dashboard_data.php` - Fixed include paths ✅

### Testing Results:
- ✅ PHP syntax validation passed for all files
- ✅ Dashboard loads successfully at new location
- ✅ No errors detected in updated files
- ✅ AJAX file paths corrected and validated
- ✅ Browser access test successful
- ✅ **FIX APPLIED**: Fixed PROJECT_ROOT_PATH for new directory depth

### Cleanup Status:
- 📋 Original AJAX file still exists at `/app/ajax/agency_dashboard_data.php` (backup)
- 📋 Alternative AJAX file exists at `/app/views/agency/ajax/dashboard_data.php` (needs evaluation)

## Dashboard Status: READY FOR FINAL VERIFICATION

**Dashboard restructuring is complete. All core functionality moved and tested.**
**Awaiting user approval to:**
1. Clean up duplicate/backup files
2. Proceed to next component (programs/outcomes)

1. ✅ Find ALL database redirections related to dashboard
2. ⏳ Create the new directory structure
3. ⏳ Move dashboard file with updated PROJECT_ROOT_PATH paths
4. ⏳ Update all reference files
5. ⏳ Document changes for user testing
6. ⏳ Move to next component (outcomes/programs)
