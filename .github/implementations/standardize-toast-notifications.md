# Standardize Toast Notifications

## Problem
The website currently uses multiple inconsistent toast notification implementations:
- Global `showToast` function in `main.js` (standard)
- `ToastManager` class in `admin/toast_manager.js` (admin-specific)
- Various page-specific `showToast` implementations
- Inconsistent styling and positioning

## Goal
Standardize all toast notifications to use the global `showToast` function from `main.js` for consistency.

## Implementation Plan

### Phase 1: Audit Current Toast Usage
- [x] Identify all toast implementations across the codebase
- [x] Document which files use which toast system
- [x] Identify inconsistencies in styling and behavior

### Phase 2: Standardize Admin Pages
- [x] Update `assets/js/admin/users.js` to use global `showToast`
- [x] Update `assets/js/admin/toast_manager.js` to use global `showToast`
- [x] Update any admin pages using `ToastManager`
- [x] Remove redundant `ToastManager` class

### Phase 3: Standardize Agency Pages
- [x] Update agency pages with custom `showToast` implementations
- [x] Ensure all agency pages use global `showToast`

### Phase 4: Standardize Other Pages
- [x] Update any remaining page-specific toast implementations
- [x] Remove duplicate `showToast` functions

### Phase 5: Testing & Validation
- [x] Test toast notifications on all major pages
- [x] Verify consistent styling and positioning
- [x] Ensure no broken functionality

## Additional Fixes Applied
- [x] Fixed user status toggle button not refreshing table automatically
- [x] Removed references to deleted toast_manager.js file
- [x] Fixed table wrapper structure in manage_users.php
- [x] Removed sector references from user management (sectors were deprecated)
- [x] Updated AJAX table handler to include focal users
- [x] Fixed 404 error in table refresh by correcting AJAX URL path
- [x] Fixed JavaScript error in simple_users.js by removing non-existent element reference
- [x] Added missing hideForm method to UserFormManager

## Files Updated
- [x] `assets/js/admin/users.js` - Updated to use global showToast
- [x] `assets/js/admin/toast_manager.js` - Removed (no longer needed)
- [x] `assets/js/admin/reporting_periods.js` - Updated to use global showToast
- [x] `assets/js/admin/programs_list.js` - Updated to use global showToast
- [x] `assets/js/admin/programs_list_fixed.js` - Updated to use global showToast
- [x] `assets/js/admin/programs_admin.js` - Updated to use global showToast
- [x] `assets/js/admin/user_table_manager.js` - Updated to use global showToast
- [x] `assets/js/admin/simple_users.js` - Updated to use global showToast
- [x] `assets/js/agency/program_form.js` - Updated to use global showToast
- [x] `assets/js/outcomes/edit-outcome.js` - Updated to use global showToast
- [x] `assets/js/metric-editor.js` - Updated to use global showToast
- [x] `assets/js/report-modules/report-ui.js` - Updated to use global showToast

## Benefits
- Consistent user experience across all pages
- Unified styling and positioning
- Easier maintenance with single toast system
- Better accessibility with consistent ARIA attributes
- Reduced code duplication 